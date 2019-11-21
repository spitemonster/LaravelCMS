<template>
    <div class="page dropzone">
        <h1>Media</h1>
        <div class="img-grid" v-if="media">
            <template v-for="m in media">
                <div class="img-grid__image" :data-fileid="m.file_id" :data-src="m.url" :data-alt="m.alt_text">
                    <img :src="m.url" :alt="m.alt_text">
                    <button @click="alert(m.file_id)" class="btn-icon">X</button>
                </div>
            </template>
        </div>
        <div class="empty" v-else>
            <h3>No media to show.</h3>
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

                        return this.media = arr
                    }

                    this.media = null
                })
        },
        alert(fileId) {
            let fileData = {
                intent: 'delete',
                type: 'media',
                method: 'deleteTarget',
                id: fileId,
                msg: 'WARNING: This will permanently delete this file. Any places where this content is linked may display an error.'
            }

            Bus.$emit('alert', fileData)
        },
        uploadFile(file) {
            Bus.$emit('uploadMedia', file)
        },
        preventDefaults(e) {
            e.preventDefault()
            e.stopPropagation()
        },
        uploadFiles(files) {
            [...files].forEach((file) => {
                this.uploadFile(file)
            })
        },
        highlight() {
            let dropzone = document.querySelector('.dropzone');

            dropzone.classList.add('highlighted');
        },
        unhighlight() {
            let dropzone = document.querySelector('.dropzone');

            dropzone.classList.remove('highlighted');
        }
    },
    mounted() {

        let dropzone = document.querySelector('.dropzone');

        this.getMedia();

        Bus.$on('mediaUploaded', this.getMedia)
        Bus.$on('deleted', (type) => {
            if (type === 'media') {
                this.getMedia()
            }
        })

        ;
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.preventDefaults, false)
        })

        ;
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.highlight, false)
        })

        ;
        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.unhighlight, false)
        })

        dropzone.addEventListener('drop', (e) => {
            let dt = e.dataTransfer;
            let files = dt.files;

            this.uploadFiles(files);
        })
    }
}

</script>
<style lang="css">
</style>
