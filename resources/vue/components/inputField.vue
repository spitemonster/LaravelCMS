<template>
    <div v-bind:class="inputClass">
        <template v-if="fieldType === 'text'">
            <input type="text" :name="fieldName" :data-fieldid="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err: invalid}" @input="fieldContent($event)" :value="content">
            <label :for="fieldName">{{ fieldName }}<template v-if="fieldRequired">*</template></label>
        </template>
        <template v-else-if="fieldType === 'wysiwyg'">
            <div id="quillEditor">
                <div class="ql-editor" data-gramm="false" contenteditable="true"><span v-html="content"></span></div>
            </div>
            <div class="image-details">
                <label>Image Width <input type="text" name="img-width"></label>
                <label>Image Height <input type="text" name="img-height"></label>
                <label>Alt Text <input type="text" name="img-alt" /></label>
            </div>
        </template>
        <template v-else-if="fieldType === 'media'">
            <form action="/media" method="POST" enctype="multipart/form-data" >
                <input type="file" id="file" name="file" accept="image/png, image/jpeg" @change="uploadFile">
            </form>
            <input type="hidden" :data-fieldid="fieldId" name="file" :value="content" id="fileUrl">
            <h4>{{ content }}</h4>
        </template>
    </div>
</template>
<script>
    import Bus from '../../js/admin.js'
    import Quill from 'quill'
    import { ImageUpload } from 'quill-image-upload';
    import axios from 'axios'

    export default {
        data () {
            return {
                invalid: false,
            }
        },
        computed: {
            inputClass() {
                if (this.fieldType === 'text') {
                    return 'input--text'
                } else if (this.fieldType === 'wysiwyg') {
                    return 'input--wysiwyg'
                } else if (this.fieldType === 'media') {
                    return 'input--media'
                }
            }
        },
        props: ['fieldType', 'fieldName', 'fieldId', 'fieldRequired', 'content'],
        methods: {
            fieldContent (e) {
                Bus.$emit('fieldFill', e.target)
            },
        },
        mounted () {
            let input = document.createElement('input');

            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ];

            if (this.fieldType === 'wysiwyg') {
                Quill.register('modules/imageUpload', ImageUpload);

                var editor = new Quill('#quillEditor', {
                    debug: 'warn',
                    modules: {
                        toolbar: toolbarOptions,
                        imageUpload: {
                            url: '/media',
                            method: 'POST',
                            name: 'file',
                            headers: {},
                            customUploader: (file, next) => {
                                // the out of the box upload was not working in the slightest, so I switched to good ol fashioned axios upload that works so well.
                                let formData = new FormData();
                                let imageFile = file
                                let url;

                                console.log(this.$route.params.page_id);
                                formData.append('file', imageFile)

                                axios.post('/media', formData, {
                                    headers: {
                                      'Content-Type': 'multipart/form-data'
                                    }
                                }).then((res) => {
                                    let growlerData = {
                                        mode: res.data.status,
                                        message: res.data.message
                                    }

                                    Bus.$emit('growl', growlerData);
                                    url = res.data.url
                                    next(url);
                                }).then(() => {
                                    let box = this.$el.querySelector('.image-details')
                                    let media = box.querySelector("input[name='img-alt']")
                                    let width = box.querySelector("input[name='img-width']")
                                    let height = box.querySelector("input[name='img-height']")
                                    let targetImg = this.$el.querySelector(`img[src="${url}"]`)

                                    targetImg.classList.add('selected-image');
                                    box.classList.add('active')

                                    width.value = targetImg.offsetWidth
                                    height.value = targetImg.offsetHeight
                                })
                            },
                            callbackOK: (serverResponse, next) => {
                                next(serverResponse)
                            },
                            callbackKO: serverError => {
                                alert(serverError)
                            },
                            checkBeforeSend: (file, next) => {
                                next(file)
                            }
                        }
                    },
                    theme: 'snow'
                });

                editor.on('text-change', (delta, oldDelta, source) => {
                    // because our fieldFill event requires a fieldid data attribute, we are using an invisible input element to hold all the data and fieldid
                    let content = document.querySelector('.ql-editor').innerHTML

                    input.dataset.fieldid = this.fieldId
                    input.value = content

                    Bus.$emit('fieldFill', input)
                })
            }

            Bus.$on('invalidField', (fieldId) => {
                if (this.fieldId === fieldId) {
                    this.invalid = true;
                }
            })

            if (this.fieldType === 'wysiwyg') {
                let q = this.$el.querySelector('.ql-editor')
                let qimg = q.querySelectorAll('img')
                let box = document.querySelector('.image-details')
                let media = box.querySelector("input[name='img-alt']")
                let width = box.querySelector("input[name='img-width']")
                let height = box.querySelector("input[name='img-height']")

                q.addEventListener('click', (e) => {
                    let t = e.target
                    if (t.tagName === 'IMG') {

                        let selected = document.querySelectorAll('.selected-image');

                        selected.forEach((image) => {
                            image.classList.remove('selected-image');
                        });

                        t.classList.add('selected-image');

                        box.classList.add('active');

                        media.value = document.querySelector('.selected-image').getAttribute('alt')
                        width.value = document.querySelector('.selected-image').offsetWidth
                        height.value = document.querySelector('.selected-image').offsetHeight
                    }
                })

                media.addEventListener('change', () => {
                    let targetImg = document.querySelector('.selected-image');

                    targetImg.setAttribute('alt', media.value);
                })

                media.addEventListener('blur', () => {
                    box.classList.remove('active');
                })

                width.addEventListener('change', () => {
                    let targetImg = document.querySelector('.selected-image')

                    targetImg.setAttribute('width', width.value)
                    height.value = targetImg.offsetHeight
                })

                height.addEventListener('change', () => {
                    let targetImg = document.querySelector('.selected-image')

                    targetImg.setAttribute('height', height.value)
                    width.value = targetImg.offsetWidth
                })
            }
        }
    }
</script>
<style lang="css">
.err {
    border: 2px solid red;
}

.ql-editor img, p {
    position: relative;
}

.image-details {
    position: absolute;
    bottom: 50%;
    left: 960px;
    height: 72px;
    width: 250px;
    background: red;
    display: none;
}

.active {
    display: block;
}

.selected-image {
    box-sizing: content-box;
    border: 2px solid slateblue;
}
</style>