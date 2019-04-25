<template>
    <div>
        <label :for="fieldName">{{ fieldName }} <template v-if="fieldRequired">*</template></label>
        <template v-if="fieldType === 'text'">
            <label :for="fieldName">{{ fieldName }}</label>
            <input type="text" :name="fieldName" :data-fieldid="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err: invalid}" @input="fieldContent($event)" :value="content">
        </template>
        <!-- <template v-else-if="fieldType === 'textarea'">
            <textarea :name="fieldName" :data-fieldId="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err : invalid}" @input="fieldContent($event)">{{ content }}</textarea>
        </template> -->
        <template v-else-if="fieldType === 'wysiwyg'">
            <div id="quillEditor">
                <div class="ql-editor" data-gramm="false" contenteditable="true"><span v-html="content"></span></div>
            </div>
            <!-- <form action="" method="get" accept-charset="utf-8">
                <input type="hidden" name="content" :value="content" :name="fieldName" :data-fieldId="fieldId" :required="fieldRequired ? true : false" id="x" class="inputField">
                <trix-editor input="x"></trix-editor>
            </form> -->
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
    // import Trix from 'trix'
    import Quill from 'quill'
    import { ImageUpload } from 'quill-image-upload';
    import axios from 'axios'

    export default {
        data () {
            return {
                invalid: false,
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
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],

                [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']                                         // remove formatting button
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
                    let content = document.querySelector('.ql-editor').innerHTML

                    input.dataset.fieldid = this.fieldId
                    input.value = content

                    Bus.$emit('fieldFill', input)
                })
            }

            // document.addEventListener('trix-change', () => {
            //     let content = document.querySelector('#x')

            //     Bus.$emit('fieldFill', content)
            // })

            Bus.$on('invalidField', (fieldId) => {
                if (this.fieldId === fieldId) {
                    this.invalid = true;
                }
            })

            // document.addEventListener('trix-attachment-add', (e) => {
            //     let formData = new FormData();
            //     let imageFile = e.attachment

            //     formData.append('file', imageFile.file)

            //     console.log(formData);

            //     axios.post('/media', formData, {
            //         headers: {
            //           'Content-Type': 'multipart/form-data'
            //         }
            //     }).then((res) => {
            //         let attributes = {
            //             url: res.data.url,
            //             href: res.data.url
            //         }

            //         e.attachment.setAttributes(attributes)
            //     })
            // })
        }
    }
</script>
<style lang="css">
@import "~vue-wysiwyg/dist/vueWysiwyg.css";

.ql-snow {
    background: white;
    width: 960px;
}

.ql-editor {
    height: 480px;
}

.err {
    border: 2px solid red;
}
</style>