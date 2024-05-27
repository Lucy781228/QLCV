<template>
  <div class="files">
    <input type="file" @change="handleFileChange" />
    <ul>
      <li v-for="file in files" :key="file.file_id">
        <NcListItem :title="file.file_name" :bold="false" :details="file.mtime">
          <template #subtitle>
            {{ file.size }}
          </template>
          <template #actions>
            <NcActionButton @click="downloadFile(file.file_id)">
              Download
            </NcActionButton>
            <NcActionButton @click="deleteFile(file.file_id)">
              Delete
            </NcActionButton>
          </template>
        </NcListItem>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "@nextcloud/axios";
import { getCurrentUser } from '@nextcloud/auth'
import { NcActionButton, NcListItem } from "@nextcloud/vue";

export default {
  name: 'File',
  components: {
    NcListItem,
    NcActionButton,
  },
  props: {
    workId: {
      type: Number,
      required: true
    },
    assignedTo: {
      type: String,
      required: true
    },
    owner: {
      type: String,
      required: true
    },
    shareUser: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      file: null,
      files: [],
      user: getCurrentUser(),
      // shareUser: ''
    };
  },
  mounted() {
    this.getFiles();
  },


  computed: {
    shareUser() {
      return this.user.uid == this.assignedTo ? this.owner : this.assignedTo
    }
  },

  methods: {
    handleFileChange(event) {
      this.file = event.target.files[0];
      this.uploadFile();
    },
    async getFiles() {
      try {
        const response = await axios.get('/apps/qlcv/get_files', {
          params: {
            work_id: this.workId,
            share_by: this.shareUser
          }
        });
        this.files = response.data.files;


        console.log(this.files)
      } catch (error) {
        console.error('Error fetching files', error);
      }
    },
    async uploadFile() {
      if (!this.file) {
        alert('Please select a file.');
        return;
      }

      let formData = new FormData();
      formData.append('file', this.file);
      formData.append('share_with', this.shareUser);
      formData.append('work_id', this.workId);

      try {
        let response = await axios.post('/apps/qlcv/upload_file', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        console.log('File uploaded successfully', response);
        this.getFiles(); // Refresh the file list
      } catch (error) {
        console.error('Error uploading file', error);
        console.log(this.shareUser);
      }
    },
    async downloadFile(fileId) {
      try {
        const response = await axios.get(`/apps/qlcv/download_file/${fileId}/${this.shareUser}`, {
          responseType: 'blob', // Important for handling the binary data correctly
        });
        const contentDisposition = response.headers['content-disposition'];
        let fileName = 'downloaded-file';
        if (contentDisposition) {
          const fileNameMatch = contentDisposition.match(/filename="?([^"]+)"?/);
          if (fileNameMatch.length === 2)
            fileName = fileNameMatch[1];
        }
        console.log(fileName)
        // Create a URL for the blob object and trigger the download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', fileName);
        document.body.appendChild(link);
        link.click();
      } catch (error) {
        console.error('Error downloading file', error);
      }
    },
    async deleteFile(fileId) {
      try {
        await axios.delete(`/apps/qlcv/delete_file/${fileId}`);
        console.log('File deleted successfully');
        this.getFiles(); // Refresh the file list
      } catch (error) {
        console.error('Error deleting file', error);
      }
    }
  }
};
</script>

<style scoped>
.files {
  height: 540px;
}

</style>