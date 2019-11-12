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
                <fieldset>
                    <div class="select-wrap">
                        <select id="template" @change="selectTemplate">
                            <option value="">Choose Template</option>
                            <option v-for="template, k in templates" :value="template.template_id">{{ template.name }}</option>
                        </select>
                    </div>
                </fieldset>
                <inputField v-for="field in fields" :fieldType="field.type" :fieldId="field.field_id" :fieldName="field.name" :fieldRequired="field.required" :key="field.id" :content="field.value"></inputField>
                <fieldset>
                    <input type="text" id="tags" v-model="tags" required />
                    <label for="tags">Tags (Comma Separated)</label>
                </fieldset>
                <button @click="createPage" class="btn btn-primary btn--no-margin">Create Page</button>
            </div>
            <div class="content" id="tabContentTwo">
                <label class="checkbox">Show In Menu?<input type="checkbox" v-model="menu" /><span class="checkbox__box"></span></label>
                <label class="checkbox">Private Page?<input type="checkbox" v-model="private" /><span class="checkbox__box"></span></label>
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
            menu: false,
            pageDescription: '',
            tags: '',
            private: false,
            pageId: null,
            parentSlug: null
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
            let pageData = {}

            pageData.title = this.pageTitle
            pageData.url = this.generatedUrl
            pageData.description = this.pageDescription
            pageData.template_id = this.selectedTemplate
            pageData.parent_id = this.selectedParent ? this.selectedParent : ''
            pageData.tags = this.tags;
            pageData.menu = this.menu;
            pageData.private = this.private;
            pageData.fields = [];
            pageData.pageId = this.pageId;

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

                pageData.fields.push(f)
            }

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
        }
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
        }
    }
}

</script>
<style lang="css">
.disable {
    pointer-events: none;
    cursor: wait;
}

</style>
