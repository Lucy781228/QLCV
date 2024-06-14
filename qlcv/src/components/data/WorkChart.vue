<template>
    <div class="main-container" v-if="loaded">
        <NcButton type="tertiary" aria-label="Example text" @click="closeMenu">
        <template #icon>
          <ArrowLeft :size="20" />
        </template>
      </NcButton>
        <div class=line-item1>
            <div class="icon-container">
                <div class="text-container">
                    <span>Công việc và số tác vụ hoàn thành</span>
                </div>
                <div class="icons">
                    <NcButton aria-label="Example text" type="tertiary" @click="downloadBarChart">
                        <template #icon>
                            <ArrowCollapseDown :size="14" />
                        </template>
                    </NcButton>
                </div>
            </div>
            <div class="container">
                <div class="chart-container">
                    <BarChart :chart-data="worksData.chartData" :options="worksData.chartOptions" ref="worksData"
                        :width="1000" :height="570"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BarChart from './BarChart.vue'
import { getCurrentUser } from '@nextcloud/auth'
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import FilterVariant from 'vue-material-design-icons/FilterVariant'
import ArrowCollapseDown from 'vue-material-design-icons/ArrowCollapseDown'
import { NcModal, NcButton } from "@nextcloud/vue";
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'

export default {
    name: 'WorkChart',
    components: { BarChart, NcButton, FilterVariant, ArrowCollapseDown, ArrowLeft },
    props: {
        startDate: {
            type: Number,
            required: true
        },

        endDate: {
            type: Number,
            default: true
        }
    },
    data() {
        return {
            user: getCurrentUser(),
            loaded: false,
            works: [],
            worksData: {
                chartData: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Hoàn thành',
                            backgroundColor: '#006AA3',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 30,
                            categoryPercentage: 0.5,
                        },
                        {
                            label: 'Chưa hoàn thành',
                            backgroundColor: '#66B2E0',
                            data: [],
                            // maxBarThickness: 8,
                            barPercentage: 0.8,
                            barThickness: 30,
                            categoryPercentage: 0.5,
                        },
                    ]
                },
                chartOptions: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        xAxes: [{
                            stacked: true,
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            stacked: true,
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) {
                                    if (Number.isInteger(value)) {
                                        return value;
                                    }
                                }
                            }
                        }],
                    },
                    legend: {
                        position: 'top'
                    },
                    tooltips: {
                        enabled: true,
                    }
                },
            }
        }
    },

    async mounted() {
        await this.fetchData()
    },

    computed: {
        receivedProjectID() {
            return this.$store.state.sharedProjectID
        },
        receivedUserID() {
            return this.$store.state.sharedProjectOwner
        },
    },

    methods: {
        async getWorks() {
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/works'), {
                    params: {
                        project_id: this.receivedProjectID,
                        user_id: this.receivedUserID,
                        assigned_to: this.user.uid
                    }
                });
                this.works = response.data.works;
            } catch (e) {
                console.error(e)
            }
        },

        async fetchData() {
            this.loaded = false;
            try {
                const response = await axios.get(generateUrl('/apps/qlcv/works'), {
                    params: {
                        project_id: this.receivedProjectID,
                        user_id: this.receivedUserID,
                        assigned_to: this.user.uid
                    }
                });

                const works = response.data.works;

                const taskData = await Promise.all(works.map(async (work) => {
                    const taskCountResponse = await axios.get(generateUrl(`/apps/qlcv/task_per_works/${work.work_id}`));
                    const undoneCountResponse = await axios.get(generateUrl(`/apps/qlcv/undone_task_per_works/${work.work_id}`));
                    return {
                        work_id: work.work_id,
                        work_name: work.work_name,
                        done_task: taskCountResponse.data.count - undoneCountResponse.data.count,
                        undone_task: undoneCountResponse.data.count
                    };
                }));

                this.updateChartData(taskData);
                this.loaded = true;
            } catch (e) {
                console.error('Error fetching data:', e);
            }
        },

        updateChartData(taskData) {
            this.worksData.chartData.labels = taskData.map(work => work.work_name);
            this.worksData.chartData.datasets[0].data = taskData.map(work => work.done_task);
            this.worksData.chartData.datasets[1].data = taskData.map(work => work.undone_task);
        },

        closeMenu() {
      this.$emit('back-to-worklist');
      console.log('from workmenu')
      this.$router.push({ name: 'project', params: { receivedProjectID: this.$route.params.sharedProjectID } });

    },

        downloadBarChart() {
            let chart = this.$refs.worksData.$data._chart;
            if (chart) {
                let url = chart.toBase64Image();
                let link = document.createElement('a');
                link.href = url;
                link.download = 'chart.png';
                link.click();
            } else {
                console.error('Chart instance not found');
            }
        }
    }
}
</script>

<style scoped>
.container {
    overflow-x: auto;
    max-width: 100%;
    width: 100%;
}

.main-container {
    display: grid;
    grid-template-columns: 1fr;
    grid-gap: 20px;
    padding: 40px;
    width: 100%;
    height: 100%
}

.line-item1 {
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.3s ease;
    height: 100%;
}

.icon-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.text-container {
    font-size: 16px;
    font-weight: bold;
}

.icons {
    display: flex;
    align-items: center;
    visibility: hidden;
}

.line-item1:hover {
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.line-item1:hover .icons {
    visibility: visible;
}
</style>