<template>
    <div class="media-window dropzone">
        <div class="img-grid" v-if="media">
            <template v-for="m in media">
                <div class="img-grid__image" :data-fileid="m.file_id" :data-src="m.url" :data-alt="m.alt_text" @click="selectFile">
                    <img :src="m.url" :alt="m.alt_text">
                    <button @click="alertDelete(m.file_id)" class="btn-icon">X</button>
                </div>
            </template>
        </div>
        <div class="button-row">
            <button @click="insertFiles()">Insert</button>
        </div>
        <button class="btn close" @click="closeWindow()">X</button>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'
import axios from 'axios';

export default {
    name: 'mediaWindow',
    data() {
        return {
            media: null,
            selected: []
        }
    },
    props: [],
    methods: {
        getMedia() {
            axios.get('/media')
                .then((res) => {
                    let arr = []

                    res.data.forEach((item) => {
                        arr.push(item);
                    })

                    this.media = arr
                })
        },
        selectFile(e) {
            let t;
            let f = {}
            if (!e.target.tagName === 'DIV' || !e.target.parentElement.tagName === 'DIV' || e.target.tagName === 'BUTTON') {
                return;
            }

            if (e.target.tagName === 'DIV') {
                t = e.target;
            } else if (e.target.parentElement.tagName === 'DIV') {
                t = e.target.parentElement
            }

            if (t.classList.contains('selected')) {
                t.classList.remove('selected')
                return this.selected = this.selected.filter(f => f.file_id !== t.dataset.fileid)
            }

            t.classList.add('selected')

            f.url = t.dataset.src;
            f.file_id = t.dataset.fileid;
            f.alt = t.dataset.alt

            this.selected.push(t)
        },
        insertFiles() {
            let fieldId = this.$el.dataset.fieldId;

            Bus.$emit('insertFiles', this.selected, fieldId);
            this.selected = [];
            this.closeWindow();
        },
        uploadFiles() {
            let f = document.querySelector('input[type="file"]');

            Bus.$emit('uploadMedia', f.files[0])
        },
        closeWindow() {
            this.$el.classList.remove('open');
        },
        alertDelete(fileId) {
            let fileData = {
                type: 'media',
                id: fileId,
                msg: 'WARNING: This will permanently delete this file. Any places where this content is linked may display an error.'
            }

            Bus.$emit('alertDelete', fileData)
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

        this.getMedia()

        // when a new image/media is added, refresh the list
        Bus.$on('mediaUploaded', this.getMedia)

        // when an image/media is deleted, refresh the list
        Bus.$on('deleted', (type) => {
            if (type === 'media') {
                this.getMedia()
            }
        })

        Bus.$on('insertFiles', () => {

            //when an image is inserted into the wysiwyg editor, remove all selected images so if there's another image to insert we start fresh
            let selectedImgs = this.$el.querySelectorAll('.selected')

            for (let i = 0; i < selectedImgs.length; i++) {
                selectedImgs[i].classList.remove('selected')
            }

            this.selected = []
        })
    }
}

</script>
<style lang="css">
</style>
