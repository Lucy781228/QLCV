<template>
    <div class="wrapper">
        <div class="menu">
            <NcDatetimePicker format="DD/MM/YYYY" class="nc-picker" :clearable="true" placeholder="Ngày bắt đầu" 
                v-model="startDate" />
            <NcDatetimePicker format="DD/MM/YYYY" class="nc-picker" :clearable="true" placeholder="Ngày kết thúc"
                v-model="endDate" />
            <NcButton aria-label="Example text" type="primary" @click="showChart">
                Áp dụng
            </NcButton>
        </div>
        <div class="content">
            <ProjectChart :start-date="dateFormatter(start)" :end-date="dateFormatter(end)" />
        </div>
    </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { NcButton, NcDatetimePicker, NcMultiselect } from "@nextcloud/vue";
import ProjectChart from './ProjectChart.vue'
// import { options } from "linkifyjs";

export default {
    name: 'DataMenu',
    components: {
        NcButton,
        NcDatetimePicker,
        NcMultiselect,
        ProjectChart
    },
    data() {
        return {
            start: null,
            end: null,
            startDate: null,
            endDate: null,
        }
    },
    methods: {
        showChart() {
            this.start = this.startDate
            this.end = this.endDate
        },

        dateFormatter(date) {
            if(!date) return 0
            return Math.floor(new Date(date).getTime()/1000)
        },
    },
}
</script>

<style scoped>
.wrapper {
    display: flex;
    flex-direction: column; /* Sắp xếp nội dung theo hướng dọc */
    padding: 20px 50px;
    width: 100%;
    height: 100%;
}

.menu {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    margin-bottom: 10px;
}

.content {
    flex: 1; /* Chiếm toàn bộ không gian còn lại */
}

.menu NcButton {
    display: inline-block;
}

input {
    height: 44px !important
}

::v-deep .mx-input {
    height: 44px !important;
}

::v-deep .multiselect {
    min-width: auto !important;
    width: 150px !important;
}

::v-deep .multiselect__tags {
    border: 2px solid #949494 !important;
}

::v-deep .multiselect__tags:hover {
    border-color: #3287b5 !important;
}

::v-deep .nc-picker {
    width: 150px !important;
}
</style>