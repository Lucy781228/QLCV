<template>
  <div class="container" v-if="isLoaded">
    <div class="action-button">
      <NcButton type="tertiary" aria-label="Example text" @click="closeMenu">
        <template #icon>
          <ArrowLeft :size="20" />
        </template>
      </NcButton>
      <NcButton :type="chartButtonType" aria-label="Example text" @click="toggleChartButtonType">
        <template #icon>
          <ChartLineVariant :size="20" />
        </template>
      </NcButton>
    </div>
    <div class="legend">
      <div class="legend-item">
        <div class="legend-color initial-duration"></div>
        <span>Thời gian hoàn thành ban đầu</span>
      </div>
      <div class="legend-item">
        <div class="legend-color actual-duration"></div>
        <span>Thời gian hoàn thành thực tế</span>
      </div>
      <div class="legend-item" v-if="chartButtonType == 'primary'">
        <div class="legend-color estimated-duration"></div>
        <span>Thời gian hoàn thành dự kiến (tham khảo)</span>
      </div>
    </div>
    <Gantt class="left-container" :tasks="workData"></Gantt>
  </div>
</template>

<script>
import Gantt from './GanttChart.vue'
import ChartLineVariant from 'vue-material-design-icons/ChartLineVariant.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import { NcButton } from "@nextcloud/vue";
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'

export default {
  name: 'GanttView',
  components: {
    Gantt,
    ChartLineVariant,
    ArrowLeft,
    NcButton
  },
  data() {
    return {
      tasks: {
        "data": [
          { "id": "2", "text": "Work 1", "start_date": "23-05-2024", "duration": 10, "actual_duration": 8 },
          { "id": "4", "text": "Work 2", "start_date": "25-05-2024", "duration": 3, "actual_duration": 5 },
          { "id": "1", "text": "Work 3", "start_date": "29-05-2024", "duration": 9, "estimated_duration": 6 },
          { "id": "3", "text": "Work 4", "start_date": "30-05-2024", "duration": 11 },
          { "id": "5", "text": "Work 5", "start_date": "03-06-2024", "duration": 10, "estimated_duration": 7 },
          { "id": "6", "text": "Work 6", "start_date": "06-06-2024", "duration": 15, "estimated_duration": 10 }
        ]
      },
      user: getCurrentUser(),
      workData: null,
      isLoaded: false,
      chartButtonType: 'tertiary',
    }
  },
  computed: {
    receivedProjectID() {
      return this.$store.state.sharedProjectID
    },
    receivedUserID() {
      return this.$store.state.sharedProjectOwner
    },
  },

  watch: {
    receivedProjectID: {
      immediate: true,
      handler(newVal) {
        if (newVal && this.receivedProjectID) {
          console.log('getWorks')
          this.getWorks()
        }
      }
    },
  },
  methods: {
    async getWorks() {
      this.isLoaded = false
      try {
        const response = await axios.get(generateUrl('/apps/qlcv/works'), {
          params: {
            project_id: this.receivedProjectID,
            user_id: this.receivedUserID,
            assigned_to: this.user.uid
          }
        });
        const works = response.data.works;

        const formattedWorks = works.map(work => {
          const startDate = new Date(work.start_date * 1000);
          const endDate = work.end_date ? new Date(work.end_date * 1000) : null;
          const actualEndDate = work.actual_end_date ? new Date(work.actual_end_date * 1000) : null;
          const formattedStartDate = `${startDate.getDate()}-${startDate.getMonth() + 1}-${startDate.getFullYear()}`;
          const duration = endDate ? Math.ceil((endDate - startDate) / (24 * 3600 * 1000)) : 0;
          const actualDuration = actualEndDate ? Math.ceil((actualEndDate - startDate) / (24 * 3600 * 1000)) : 0;

          console.log(formattedStartDate)
          return {
            id: work.work_id.toString(),
            text: work.work_name,
            start_date: formattedStartDate,
            duration: duration,
            estimated_duration: 0,
            actual_duration: actualDuration
          };
        });
        this.workData = { "data": formattedWorks }
        this.isLoaded = true

      } catch (e) {
        console.error(e);
      }
    },

    async predictWork() {
      try {
        const response = await axios.get(generateUrl(`/apps/qlcv/data/predict_work/${this.receivedProjectID}`));
        const predictions = response.data.data;
        const currentDate = new Date();
        currentDate.setHours(0, 0, 0, 0);
        const currentTimestamp = Math.floor(currentDate.getTime() / 1000);

        predictions.forEach((pred) => {
          const workIndex = this.workData.data.findIndex(work => work.id === pred.work_id.toString());
          if (workIndex !== -1) {
            console.log(this.workData.data[workIndex].start_date)
            const startDateTimestamp = Math.floor(this.workData.data[workIndex].start_date.getTime() / 1000)

            if (startDateTimestamp + pred.predicted_duration >= currentTimestamp) {
              this.workData.data[workIndex].estimated_duration = Math.round(pred.predicted_duration / (60 * 60 * 24));
            }
          }
        });

        this.isLoaded = true;
      } catch (e) {
        console.error(e);
      }
    },

    toggleChartButtonType() {
      this.chartButtonType = this.chartButtonType === 'tertiary' ? 'primary' : 'tertiary'
      if (this.chartButtonType == 'primary') this.predictWork()
      else this.getWorks()
    },

    closeMenu() {
      this.$emit('back-to-worklist');
      console.log('from workmenu')
      this.$router.push({ name: 'project', params: { receivedProjectID: this.$route.params.sharedProjectID } });

    },
  },

}
</script>

<style scoped>
@import "~dhtmlx-gantt/codebase/dhtmlxgantt.css";


.container {
  height: 100%;
  width: 100%;
  padding: 40px;
}

.left-container {
  overflow: hidden;
  position: relative;
  height: 90%;
}

.action-button {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-bottom: 5px;
}

.legend {
  display: flex;
  justify-content: center;
  margin-bottom: 10px;
  margin: 0 auto
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  padding-left: 15px;
  padding-right: 15px;
}

.legend-color {
  width: 30px;
  height: 15px;
}

.actual-duration {
  background-color: #3DB9D3;
  background-image: linear-gradient(45deg,
      rgba(255, 255, 255, 0.4) 25%,
      transparent 25%,
      transparent 50%,
      rgba(255, 255, 255, 0.4) 50%,
      rgba(255, 255, 255, 0.4) 75%,
      transparent 75%,
      transparent);
  background-size: 30px 30px;
  color: white;
  border: none;
}

.estimated-duration {
  border: 2px dashed #000;
  border-radius: 3px;
  background-color: white;
}

.initial-duration {
  background-color: #3DB9D3;
}
</style>