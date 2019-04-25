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
                                // the out of the box upload was not working in the slightest, so I switched to the good ol fashioned axios upload that works so well.
                                let formData = new FormData();
                                let imageFile = file

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

                                    next(res.data.url);
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
        }
    }
</script>
<style lang="css">
.err {
    border: 2px solid red;
}
</style>