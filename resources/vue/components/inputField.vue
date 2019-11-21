<template>
    <div v-bind:class="inputClass" class="input-field">
        <template v-if="fieldType === 'text'">
            <input type="text" :name="fieldName" :data-fieldid="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err: invalid}" @input="fieldContent($event)" :value="content">
            <label :for="fieldName">{{ fieldName }}<template v-if="fieldRequired">*</template></label>
        </template>
        <template v-else-if="fieldType === 'wysiwyg'">
            <div class="toolbar" :data-field-id="fieldId">
                <button class="ql-bold"></button>
                <button class="ql-italic"></button>
                <button class="ql-underline"></button>
                <button class="ql-strike"></button>
                <button class="ql-script" value="sub"></button>
                <button class="ql-script" value="super"></button>
                <button class="ql-list" value="ordered"></button>
                <button class="ql-list" value="bullet"></button>
                <select class="ql-header">
                    <option value="1">H1</option>
                    <option value="2">H2</option>
                    <option value="3">H3</option>
                    <option value="4">H4</option>
                    <option value="5">H5</option>
                    <option value="6">H6</option>
                </select>
                <button @click="openMedia(fieldId)"><svg class="ql-img" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M5 8.5c0-.828.672-1.5 1.5-1.5s1.5.672 1.5 1.5c0 .829-.672 1.5-1.5 1.5s-1.5-.671-1.5-1.5zm9 .5l-2.519 4-2.481-1.96-4 5.96h14l-5-8zm8-4v14h-20v-14h20zm2-2h-24v18h24v-18z" /></svg></button>
            </div>
            <div class="editor" :data-field-id="fieldId">
                <div class="ql-editor" data-gramm="false" contenteditable="true"><span v-html="content"></span></div>
            </div>
            <div class="image-details">
                <label>Image Width <input type="text" name="img-width"></label>
                <label>Image Height <input type="text" name="img-height"></label>
                <label>Alt Text <input type="text" name="img-alt" /></label>
            </div>
        </template>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'
import Quill from 'quill'
import axios from 'axios'
import Delta from 'quill-delta'

export default {
    data() {
        return {
            invalid: false,
            pageId: null,
            index: null,
            files: [],
            vf: []
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
        },
    },
    props: ['fieldType', 'fieldName', 'fieldId', 'fieldRequired', 'content'],
    methods: {
        fieldContent(e) {
            Bus.$emit('fieldFill', e.target)
        },
        openMedia(fieldId) {
            Bus.$emit('openMedia', fieldId)
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
        let input = document.createElement('input');
        if (this.fieldType === 'wysiwyg') {
            let q = this.$el.querySelector('.ql-editor')
            let qimg = q.querySelectorAll('img')
            let box = document.querySelector('.image-details')
            let media = box.querySelector("input[name='img-alt']")
            let imageWidth = box.querySelector("input[name='img-width']")
            let imageHeight = box.querySelector("input[name='img-height']")
            let BlockEmbed = Quill.import('blots/block/embed');

            // implement and register the imageblot for the custom image insert feature, since we don't upload directly within the wysiwyg
            class ImageBlot extends BlockEmbed {
                static create(value) {
                    let node = super.create();
                    node.setAttribute('alt', value.alt);
                    node.setAttribute('src', value.src);
                    return node;
                }

                static value(node) {
                    return {
                        alt: node.getAttribute('alt'),
                        src: node.getAttribute('src')
                    };
                }
            }

            ImageBlot.blotName = 'image';
            ImageBlot.tagName = 'img';

            Quill.register({ 'formats/image': ImageBlot });

            // set up the editor
            // we target by classes with field IDs to keep things separate but legible
            let editor = new Quill(`.editor[data-field-id="${this.fieldId}"]`, {
                debug: 'warn',
                modules: {
                    toolbar: {
                        container: `.toolbar[data-field-id="${this.fieldId}"]`,
                    },
                },
                theme: 'snow'
            });

            // keep track of where the cursor is in the editor OUTSIDE of editor events so that we don't lose position when an image is being inserted
            editor.on('editor-change', (eventName, ...args) => {
                let content = this.$el.querySelector('.ql-editor').innerHTML

                if (editor.getSelection() !== null) {
                    this.editorIndex = editor.getSelection().index
                } else { this.editorIndex === 0 }

                // because our fieldFill event requires a fieldid data attribute, we are using an invisible input element to hold all the data and fieldid
                input.dataset.fieldid = this.fieldId
                input.value = content

                Bus.$emit('fieldFill', input)
            })

            // when we get an insert files event, check if the field id matches our current field id
            // if so, go on and embed that image, son
            Bus.$on('insertFiles', (files, fieldId) => {
                if (this.fieldId === fieldId) {
                    files.forEach((file) => {
                        editor.insertEmbed(this.editorIndex, 'image', {
                            src: file.dataset.src, // any url
                            alt: file.dataset.alt
                        }, 'user');
                    })
                }
            })

            // this is where we deal with adjusting the size and alt attributes of an image
            // doesn't work great right now

            document.addEventListener('click', (e) => {
                let t = e.target
                if (t.tagName === 'IMG' || t === box) {

                    let selected = document.querySelectorAll('.selected-image');

                    selected.forEach((image) => {
                        image.classList.remove('selected-image');
                    });

                    t.classList.add('selected-image');

                    box.classList.add('active');

                    media.value = document.querySelector('.selected-image').getAttribute('alt')
                    imageWidth.value = document.querySelector('.selected-image').offsetWidth
                    imageHeight.value = document.querySelector('.selected-image').offsetHeight
                } else {

                    // if user clicks in the editor and they are not selecting an image, get rid of the box
                    if (document.querySelector('.selected-image')) {
                        document.querySelector('.selected-image').classList.remove('selected-image')
                    }

                    box.classList.remove('active');
                }
            })

            media.addEventListener('change', () => {
                let targetImg = document.querySelector('.selected-image');

                targetImg.setAttribute('alt', media.value);
            })

            media.addEventListener('blur', () => {
                box.classList.remove('active');
            })

            imageWidth.addEventListener('change', () => {
                let targetImg = document.querySelector('.selected-image')

                targetImg.setAttribute('width', imageWidth.value)
                imageHeight.value = targetImg.offsetHeight
            })

            imageHeight.addEventListener('change', () => {
                let targetImg = document.querySelector('.selected-image')

                targetImg.setAttribute('height', imageHeight.value)
                imageWidth.value = targetImg.offsetWidth
            })
        }

        // Should be self documenting, but if a field is invalid, get the fieldID, find the field and mark it invalid
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

.ql-editor img,
p {
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

</style>
