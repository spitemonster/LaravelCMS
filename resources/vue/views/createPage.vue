<template>
    <div class="page">
        <h1>Create Page</h1>
        <!-- <h2>{{ selectedParent }}</h2> -->
        <input type="radio" name="tab" id="tabOne" checked>
        <input type="radio" name="tab" id="tabTwo">
        <fieldset>
            <input type="text" id="pageName" v-model="pageTitle" @change="generateUrl" required />
            <label for="pageName">Page Name</label>
        </fieldset>
        <fieldset>
            <input type="text" id="pageUrl" v-model="generatedUrl" @change="getUrl(generatedUrl)" required />
            <label for="pageUrl">Page URL</label>
        </fieldset>
        <fieldset>
            <div class="select-wrap">
                <select id="template" @change="selectTemplate">
                    <option value="">Choose Template</option>
                    <option v-for="template, k in templates" :value="template.template_id">{{ template.name }}</option>
                </select>
            </div>
        </fieldset>
        <fieldset>
            <div class="select-wrap">
                <select id="parentPage" v-model="selectedParent" @change="selectParent" v-if="pages">
                    <option value="">No Parent</option>
                    <option v-for="page, k in pages" v-if="page.url !== '/'" :value="page.page_id">{{ page.title }} - {{ page.url }}</option>
                </select>
            </div>
        </fieldset>
        <label for="tabOne" class="tab">Page Content</label>
        <label for="tabTwo" class="tab">Page Details</label>
        <div class="content--wrapper">
            <div class="content" id="tabContentOne">
                <inputField v-for="field in fields" :fieldType="field.type" :fieldId="field.field_id" :fieldName="field.name" :fieldRequired="field.required" :key="field.id" :content="field.value"></inputField>
                <fieldset>
                    <div class="page-dropzone">
                        <img v-for="file in vf" :src="file" class="thumbnail" />
                    </div>
                </fieldset>
                <fieldset>
                    <input type="text" id="tags" v-model="tags" required />
                    <label for="tags">Tags (Comma Separated)</label>
                </fieldset>
                <button @click="createPage" class="btn btn-primary btn--no-margin">Create Page</button>
            </div>
            <div class="content" id="tabContentTwo">
                <label class="checkbox">Show In Menu?<input type="checkbox" v-model="menu" value="1" /><span class="checkbox__box"></span></label>
                <label class="checkbox">Private Page?<input type="checkbox" v-model="private" value="1" /><span class="checkbox__box"></span></label>
                <fieldset>
                    <textarea id="pageDescription" v-model="pageDescription" name="description" required></textarea>
                    <label for="pageDescription">Meta Description</label>
                </fieldset>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
import inputField from '../components/inputField.vue'
import Bus from '../../js/admin.js'
import uuidv4 from 'uuid/v4'
import router from '../../js/admin.js'

export default {
    data() {
        return {
            templates: {},
            fields: [],
            selectedTemplate: {},
            pages: {},
            selectedParent: '',
            pageTitle: '',
            generatedUrl: '',
            urlAvailable: false,
            iterator: 0,
            baseUrl: '',
            fieldsValid: false,
            menu: 0,
            pageDescription: '',
            tags: '',
            private: 0,
            pageId: null,
            parentSlug: null,
            files: [],
            vf: []
        }
    },
    props: [],
    computed: {},
    methods: {
        selectTemplate(e) {
            let sel = e.target

            if (sel.value !== '') {
                if (sel.classList.contains('invalid')) {
                    sel.classList.remove('invalid')
                }
                axios.get(`/template?template_id=${sel.value}`)
                    .then((res) => {
                        this.fields = res.data.fields
                        this.selectedTemplate = res.data.template_id
                    })
            }
        },
        selectParent(e) {
            let parent = e.target

            axios.get(`/page?page_id=${parent.value}`)
                .then((res) => {
                    this.parentSlug = res.data.url;

                    this.getUrl(`${this.parentSlug ? this.parentSlug : ''}${this.baseUrl}`);
                })
        },
        generateUrl(e) {
            let t = e.target
            let pageName = t.value
            let url = this.baseUrl = pageName.replace(/[\s]+/g, '-').replace(/[\s~!@#$%^&*()+={}|\\:;"'<>,.?]+/g, '').toLowerCase();
            let urlInput = this.$el.querySelector('#pageUrl')

            urlInput.classList.add('disable')

            // get page name and generate a url based on it, where e is the page name input
            if (t.classList.contains('invalid')) {
                t.classList.remove('invalid')
            }

            if (url.split('')[0] !== '/') {
                url = this.baseUrl = `/${url}`;
            }

            this.getUrl(`${this.parentSlug ? this.parentSlug : ''}${url}`);
        },
        getUrl(slug) {
            axios.get(`/url?slug=${slug}`)
                .then((res) => {
                    let url = document.querySelector('#pageUrl')

                    if (url.classList.contains('invalid')) {
                        url.classList.remove('invalid')
                    }

                    url.classList.remove('disable')

                    return this.generatedUrl = res.data;
                })
        },
        createPage() {
            let headers = { 'Content-Type': 'application/json' }

            if (!this.pageTitle || !this.generatedUrl || !this.selectedTemplate) {

                if (!this.pageTitle) {
                    document.querySelector('#pageName').classList.add('invalid')
                }

                if (!this.generatedUrl) {
                    document.querySelector('#pageUrl').classList.add('invalid')
                }

                if (!this.selectedTemplate) {
                    document.querySelector('#template').classList.add('invalid')
                }

                let growlerData = {
                    mode: 'error',
                    message: `Please fill in marked fields and try again.`
                }

                return Bus.$emit('growl', growlerData);
            }

            let pageData = this.collectPageData();

            let createData = {
                type: 'page',
                data: pageData,
                redirect: 'viewPages'
            }

            if (this.fieldsValid) {
                Bus.$emit('create', createData)
            }
        },
        collectPageData() {

            // so what do we need to do here

            // we need to find some way to upload files and associate them with a page via page id
            // but since the page id isn't generated until 'submit' is clicked we have an issue
            // can't upload files without formdata

            let pageData = new FormData()

            pageData.append('title', this.pageTitle);
            pageData.append('url', this.generatedUrl);
            pageData.append('description', this.pageDescription);
            pageData.append('template_id', this.selectedTemplate);
            pageData.append('parent_id', this.selectedParent ? this.selectedParent : '');
            pageData.append('tags', this.tags);
            pageData.append('menu', this.menu);
            pageData.append('private', this.private);
            // pageData.fields = [];
            pageData.append('pageId', this.pageId);

            for (let i = 0; i < this.fields.length; i++) {
                let field = this.fields[i];

                let f = {};

                if (!field.content && field.required) {
                    this.invalidField(field.field_id);
                    break
                }

                f.content = field.content;
                f.field_id = field.field_id;
                f.field_name = field.name;
                f.type = field.type;

                // pageData.fields.push(f)

                pageData.append('fields[]', JSON.stringify(f));
            }

            this.files.forEach((file) => {
                pageData.append('files[]', file);
            });

            this.fieldsValid = true;

            return pageData
        },
        invalidField(fieldId) {
            this.fieldsValid = false

            let growlerData = {
                mode: 'error',
                message: 'Please fill in highlighted fields and try again'
            }

            Bus.$emit('growl', growlerData);
            Bus.$emit('invalidField', fieldId);
        },
        preventDefaults(e) {
            e.preventDefault()
            e.stopPropagation()
        },
    },
    components: {
        inputField
    },
    beforeCreate() {
        axios.get('/template')
            .then((res) => {
                this.templates = res.data
            })

        axios.get('/page')
            .then((res) => {
                this.pages = res.data
            })
    },
    mounted() {
        let dropzone = document.querySelector('.page-dropzone');

        document.addEventListener('keydown', (e) => {
            if (e.metaKey && e.which == 83) {
                e.preventDefault()

                this.createPage()
            }
        })

        Bus.$on('fieldFill', (field) => {
            let targetField = field.dataset.fieldid
            this.fields.forEach((f) => {
                if (f.field_id == targetField) {

                    f.content = field.value

                }
            })
        })

        Bus.$on('imageUploaded', (pageId) => {
            this.pageId = pageId;
        })

        if (this.$route.params.parent_id) {
            axios.get(`/page?page_id=${this.$route.params.parent_id}`)
                .then((res) => {
                    this.parentSlug = res.data.url;
                })

            this.selectedParent = this.$route.params.parent_id
        };

        // Bus.$on('deleted', (type) => {
        // if (type === 'media') {
        // this.getMedia()
        // }
        // });


        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.preventDefaults, false)
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.highlight, false)
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, this.unhighlight, false)
        });

        dropzone.addEventListener('dragenter', (e) => {
            console.log('yep')
        })

        dropzone.addEventListener('drop', (e) => {
            e.preve
            let dt = e.dataTransfer;
            let files = dt.files;
            let reader = new FileReader()

            reader.onloadend = () => {
                this.vf.push(reader.result)
            };

            [...files].forEach((file) => {
                reader.readAsDataURL(file);
                this.files.push(file)
            })

            console.log(this.files)
        })
    }
}

</script>
<style lang="css">
.disable {
    pointer-events: none;
    cursor: wait;
}

.page-dropzone {
    height: 200px;
    width: 100%;
    background: red;
}

.thumbnail {
    height: 50px;
    width: 50px;
    object-position: center;
    object-fit: cover;
}

</style>
