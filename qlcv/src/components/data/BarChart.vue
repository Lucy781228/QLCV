<script>
  import { HorizontalBar, mixins } from 'vue-chartjs'
  
  export default {
    name: 'BarChart',
    extends: HorizontalBar,
    mixins: [mixins.reactiveProp],
    props: ['chartData', 'options'],
    mounted () {
      this.renderChart(this.chartData, this.options);
    }
  }
  </script>