<?php
declare(strict_types=1);

namespace OCA\QLCV\Service;

use Exception;
use OCA\QLCV\Service\WorkService;
class PredictionService {
    private $workService;

    public function __construct(WorkService $workService) {
        $this->workService = $workService;
    }

    private function trainMultipleLinearRegressionModel($project_id) {
        $data = $this->workService->getSampleData($project_id);
        if (count($data) < 3) {
            throw new \Exception("Insufficient data for training the model. At least 3 rows of data are required.");
        }
        $X = [];
        $y = [];
        foreach ($data as $row) {
            $X[] = [1, $row['num_tasks'], $row['priority']]; // Thêm 1 cho hệ số chặn (intercept)
            $y[] = $row['duration'];
        }

        // Chuyển đổi mảng thành ma trận
        $X = $this->arrayToMatrix($X);
        $y = $this->arrayToMatrix($y, true);

        // Tính toán hệ số hồi quy tuyến tính đa biến
        $X_transpose = $this->transposeMatrix($X);
        $X_transpose_X = $this->multiplyMatrices($X_transpose, $X);
        $X_transpose_y = $this->multiplyMatrices($X_transpose, $y);
        $coefficients = $this->solveLinearSystem($X_transpose_X, $X_transpose_y);

        return $coefficients;
    }

    // Hàm để chuyển đổi mảng thành ma trận
    private function arrayToMatrix(array $array, bool $isColumnVector = false) {
        $matrix = [];
        if ($isColumnVector) {
            foreach ($array as $value) {
                $matrix[] = [$value];
            }
        } else {
            $matrix = $array;
        }
        return $matrix;
    }

    // Hàm để chuyển vị ma trận
    private function transposeMatrix(array $matrix) {
        return array_map(null, ...$matrix);
    }

    // Hàm để nhân hai ma trận
    private function multiplyMatrices(array $matrixA, array $matrixB) {
        $result = [];
        for ($i = 0; $i < count($matrixA); $i++) {
            for ($j = 0; $j < count($matrixB[0]); $j++) {
                $result[$i][$j] = 0;
                for ($k = 0; $k < count($matrixB); $k++) {
                    $result[$i][$j] += $matrixA[$i][$k] * $matrixB[$k][$j];
                }
            }
        }
        return $result;
    }

    // Hàm để giải hệ phương trình tuyến tính sử dụng phương pháp Gauss-Jordan
    private function solveLinearSystem(array $A, array $b) {
        $n = count($A);
        for ($i = 0; $i < $n; $i++) {
            $A[$i][] = $b[$i][0];
        }

        for ($i = 0; $i < $n; $i++) {
            $max = $i;
            for ($j = $i + 1; $j < $n; $j++) {
                if (abs($A[$j][$i]) > abs($A[$max][$i])) {
                    $max = $j;
                }
            }
            $temp = $A[$i];
            $A[$i] = $A[$max];
            $A[$max] = $temp;

            $temp = $b[$i][0];
            $b[$i][0] = $b[$max][0];
            $b[$max][0] = $temp;

            for ($j = $i + 1; $j < $n; $j++) {
                $factor = $A[$j][$i] / $A[$i][$i];
                for ($k = $i; $k <= $n; $k++) {
                    $A[$j][$k] -= $factor * $A[$i][$k];
                }
            }
        }

        $x = array_fill(0, $n, 0);
        for ($i = $n - 1; $i >= 0; $i--) {
            $sum = 0;
            for ($j = $i + 1; $j < $n; $j++) {
                $sum += $A[$i][$j] * $x[$j];
            }
            $x[$i] = ($A[$i][$n] - $sum) / $A[$i][$i];
        }

        return $x;
    }

    public function predictWorksCompletionTimes($project_id) {
        try {
            $worksData = $this->workService->getNeededPredictionWork($project_id);
            $predictions = [];
            
            $coefficients = $this->trainMultipleLinearRegressionModel($project_id);
            
            foreach ($worksData as $work) {
                $predictedDuration = $coefficients[0] + $coefficients[1] * $work['num_tasks'] + $coefficients[2] * $work['priority'];
                $work['predicted_duration'] = round($predictedDuration, 2);
                $predictions[] = $work;
            }
            
            return $predictions;
        } catch (Exception $e) {
            throw new Exception("ERROR: " . $e->getMessage());
        }
    }

    public function simplePredict($work_id) {
        try {
            $work = $this->workService->getWorkById($work_id);  
            $durationInSeconds = (int)$work['end_date'] - (int)$work['start_date'];
            $totalTasks = $this->workService->countTaskPerWorks($work_id);
            $undoneTasks = $this->workService->countUndoneTaskPerWorks($work_id);
    
            $predictedCompletionTime = 0;
            if ($totalTasks > 0 && $undoneTasks > 0) {
                $predictedCompletionTime = ($durationInSeconds / $totalTasks) * $undoneTasks;
            }
            
            return round($predictedCompletionTime, 2);
        } catch (\Exception $e) {
            throw new \Exception("ERROR: " . $e->getMessage());
        }
    }
}