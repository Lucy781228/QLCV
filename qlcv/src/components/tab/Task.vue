<template>
    <div class="tasks">
      <div class="scrollable-list">
        <div class="task-list">
          <div v-for="(task, index) in tasks" :key="index" class="task-item">
            <input
              type="checkbox"
              :id="`task-${index}`"
              :value="task.value"
              :checked="task.checked"
              :disabled="task.disabled"
            />
            <label :for="`task-${index}`">{{ task.label }}</label>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { generateUrl } from '@nextcloud/router'
  import axios from "@nextcloud/axios";
  
  export default {
    name: 'Task',
    props: {
      workId: {
        type: Number,
        required: true
      }
    },
    data() {
      return {
        tasks: [
          { label: 'First choice', value: 'first', checked: false, disabled: false },
          { label: 'Second choice', value: 'second', checked: false, disabled: false },
          { label: 'Third choice (checked)', value: 'third', checked: true, disabled: false },
          { label: 'Second choice (disabled)', value: 'second-disabled', checked: true, disabled: true }
        ]
      };
    },
    methods: {
    }
  }
  </script>
  
  <style scoped>
  .scrollable-list {
    overflow-y: auto;
    max-height: 430px;
    height: 430px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    padding: 20px;
    word-wrap: break-word;
  }
  
  .tasks {
    height: 500px;
    padding: 20px;
  }
  
  .task-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  
  .task-item {
    display: flex;
    align-items: center;
  }
  
  .task-item input {
    margin-right: 10px;
  }
  </style>