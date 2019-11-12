<template>
    <div class="page">
        <h1>Media</h1>
        <div class="img-grid" v-if="media">
            <template v-for="m in media">
                <div class="img-grid__image" :data-fileid="m.file_id" :data-src="m.url" :data-alt="m.alt_text">
                    <img :src="m.url" :alt="m.alt_text">
                    <button @click="alertDelete(m.file_id)" class="btn-icon">X</button>
                </div>
            </template>
        </div>
        <div class="empty" v-else>
            <h3>No media to show.</h3>
        </div>
        <div class="button-row">
            <input type="file" name="somethin" id="file">
            <button class="btn" @click="uploadFiles()">Upload</button>
        </div>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'
import axios from 'axios';

export default {
    name: 'viewMedia',
    data() {
        return {
            media: null,
        }
    },
    props: [],
    methods: {
        getMedia() {
            axios.get('/media')
                .then((res) => {
                    let arr;

                    if (res.data.length) {
                        arr = [];
                        res.data.forEach((item) => {
                            arr.push(item);
                        })

                        console.log(arr)

                        return this.media = arr
                    }

                    this.media = null
                })
        },
        alertDelete(fileId) {
            let fileData = {
                type: 'media',
                id: fileId,
                msg: 'WARNING: This will permanently delete this file. Any places where this content is linked may display an error.'
            }

            Bus.$emit('alertDelete', fileData)
        },
        uploadFiles() {
            let f = document.querySelector('input[type="file"]');

            Bus.$emit('uploadMedia', f.files[0])
        }
    },
    mounted() {
        this.getMedia();
        Bus.$on('mediaUploaded', this.getMedia)
        Bus.$on('deleted', (type) => {
            if (type === 'media') {
                this.getMedia()
            }
        })
    }
}

</script>
<style lang="css">
</style>
