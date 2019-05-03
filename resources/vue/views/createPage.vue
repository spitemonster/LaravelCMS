<template>
    <div>
        <h1>Create Page</h1>
        <input type="radio" name="tab" id="tabOne" checked>
        <input type="radio" name="tab" id="tabTwo">

        <fieldset>
            <input type="text" id="pageName" v-model="pageName" @change="generateUrl" required />
            <label for="pageName">Page Name</label>
        </fieldset>

        <fieldset>
            <input type="text" id="pageUrl" v-model="generatedUrl" @change="getUrl(generatedUrl)" required />
            <label for="pageUrl">Page URL</label>
        </fieldset>

        <fieldset>
            <select id="parentPage" @change="selectParent" v-if="pages">
                <option value="">No Parent</option>
                <option v-for="page, k in pages" v-if="page.url !== '/'" :value="page.page_id">{{ page.title }} - {{ page.url }}</option>
            </select>
        </fieldset>

        <label for="tabOne" class="tab">Page Content</label>
        <label for="tabTwo" class="tab">Page Details</label>

        <div class="content--wrapper">
            <div class="content" id="tabContentOne">
                <select id="template" @change="selectTemplate">
                    <option value="">Choose Template</option>
                    <option v-for="template, k in templates" :value="template.template_id">{{ template.name }}</option>
                </select>

                <inputField v-for="field in fields"
                            :fieldType="field.type"
                            :fieldId="field.field_id"
                            :fieldName="field.name"
                            :fieldRequired="field.required"
                            :key="field.id"
                            :content="field.value"></inputField>

                <button @click="createPage">Create Page</button>

                <input type="text" id="tags" />
            </div>

            <div class="content" id="tabContentTwo">

                <label for="showInMenu">Show in Menu?</label>
                <input type="checkbox" name="menu" id="showInMenu" v-model="menu"/>

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

    export default {
        data () {
            return {
                templates: {},
                fields: [],
                selectedTemplate: {},
                pages: {},
                selectedParent: '',
                pageName: '',
                generatedUrl: '',
                urlAvailable: false,
                iterator: 0,
                baseUrl: '',
                fieldsValid: false,
                menu: false,
                pageDescription: ''
            }
        },
        props: [],
        computed: {
        },
        methods: {
            selectTemplate (e) {
                let sel = e.target.value

                if (sel !== '') {
                    if (e.target.classList.contains('invalid')) {
                        e.target.classList.remove('invalid')
                    }

                    axios.get(`/template?template_id=${sel}`)
                        .then((res) => {
                            this.fields = res.data.fields
                            this.selectedTemplate = res.data.template_id
                        })
                }
            },
            selectParent (e) {
                let parent = e.target.value
                axios.get(`/page?page_id=${parent}`)
                    .then((res) => {
                        let parentSlug = res.data.url;
                        this.selectedParent = parent;
                        this.getUrl(`${parentSlug ? parentSlug : ''}${this.baseUrl}`);
                    })
            },
            generateUrl(e) {
                // get page name and generate a url based on it, where e is the page name input
                if (e.target.classList.contains('invalid')) {
                    e.target.classList.remove('invalid')
                }

                let pageName = e.target.value
                let url = this.baseUrl = pageName.replace(/[\s]+/g, '-').replace(/[\s~!@#$%^&*()+={}|\\:;"'<>,.?]+/g, '').toLowerCase();

                if (url.split('')[0] !== '/') {
                    url = this.baseUrl = `/${url}`;
                }

                this.getUrl(url);
            },
            getUrl(slug) {
                axios.get(`/url?slug=${slug}`)
                    .then((res) => {
                        let url = document.querySelector('#pageUrl')

                        if (url.classList.contains('invalid')) {
                            url.classList.remove('invalid')
                        }

                        return this.generatedUrl = res.data;
                    })
            },
            createPage () {
                let headers = { 'Content-Type': 'application/json' }
                let name = document.querySelector('#pageName')
                let url = document.querySelector('#pageUrl')
                let template = document.querySelector('#template')

                if (!name.value || !url.value || !template.value) {

                    if (!name.value) {
                        name.classList.add('invalid')
                    }

                    if (!url.value) {
                        url.classList.add('invalid')
                    }

                    if (!template.value) {
                        template.classList.add('invalid')
                    }

                    let growlerData = {
                        mode: 'error',
                        message: `Please fill in marked fields and try again.`
                    }

                    return Bus.$emit('growl', growlerData);
                }

                let tags = document.querySelector('#tags').value;
                let pageData = {}

                pageData.title = name.value
                pageData.description = this.pageDescription
                pageData.url = url.value.toLowerCase()
                pageData.template_id = this.selectedTemplate
                pageData.parent_id = this.selectedParent ? this.selectedParent : ''
                pageData.tags = tags;
                pageData.menu = this.menu

                pageData.fields = [];

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

                if (!url.checkValidity) {
                    url.classList.add('invalid')
                    return
                }

                if (this.fieldsValid) {
                    Bus.$emit('createPage', pageData)
                }
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
        beforeCreate () {
            axios.get('/template')
              .then((res) => {
                  this.templates = res.data
              })

            axios.get('/page')
                .then((res) => {
                    this.pages = res.data
                })
        },
        mounted () {
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
        }
    }
</script>
<style lang="css">
.invalid {
    border: 3px solid red;
}
</style>