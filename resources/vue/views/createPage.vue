<template>
    <div>
        <h1>Create Page</h1>
        <input type="text" id="pageName" v-model="pageName" @change="generateUrl">
        <input type="text" id="pageUrl" v-model="generatedUrl" value="/" @change="getUrl(generatedUrl)">
        <select id="template" @change="selectTemplate">
            <option value="">Choose Template</option>
            <option v-for="template, k in templates" :value="template.template_id">{{ template.name }}</option>
        </select>

        <select id="parentPage" @change="selectParent" v-if="pages">
            <option value="">No Parent</option>
            <option v-for="page, k in pages" v-if="page.url !== '/'" :value="page.page_id">{{ page.title }} - {{ page.url }}</option>
        </select>

        <label for="showInMenu">Show in Menu?</label>
        <input type="checkbox" name="menu" id="showInMenu" />

        <inputField v-for="field in fields"
                    :fieldType="field.type"
                    :fieldId="field.id"
                    :fieldName="field.name"
                    :fieldRequired="field.required"
                    :key="field.id"></inputField>

        <button @click="createPage">Create Page</button>

        <input type="text" id="tags" />
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
            }
        },
        props: [],
        computed: {
        },
        methods: {
            selectTemplate (e) {
                let sel = e.target.value

                if (sel !== '') {
                    axios.get(`/template?template_id=${sel}`)
                        .then((data) => {
                            console.log(data.data.fields)
                            this.fields = data.data.fields
                            this.selectedTemplate = data.data
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
                        return this.generatedUrl = res.data;
                    })
            },
            createPage () {
                let url = document.querySelector('#pageUrl')
                let headers = { 'Content-Type': 'application/json' }
                let tags = document.querySelector('#tags').value;

                let pageData = {}

                pageData.title = document.querySelector('#pageName').value
                pageData.url = url.value.toLowerCase()
                pageData.template_id = this.selectedTemplate.template_id
                pageData.parent_id = this.selectedParent ? this.selectedParent : ''
                pageData.page_id = uuidv4();
                pageData.tags = tags;
                pageData.menu = document.querySelector('#showInMenu').value === 'on' ? true : false;

                pageData.fields = [];

                this.fields.forEach((field) => {
                    let obj = {};

                    obj.content = field.content;
                    obj.field_id = field.field_id;
                    obj.page_id = pageData.page_id;
                    obj.field_name = field.name;
                    obj.type = field.type;
                    pageData.fields.push(obj);
                });

                pageData.fields = pageData.fields;

                if (!url.checkValidity) {
                    url.classList.add('invalid')
                    return
                }

                axios.post('/page', pageData, headers)
                .then((res) => {
                    if (res.status === 200) {
                        this.$router.push({ name: 'viewPages' })
                        let growlerData = {
                            mode: 'success',
                            message: res.data
                        }

                        return Bus.$emit('growl', growlerData);
                    }

                    console.log(res.status)


                }).catch((err) => {
                    let growlerData = {
                        mode: 'error',
                        message: err
                    }

                    return Bus.$emit('growl', growlerData);
                })
            },
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
                    if (f.id == targetField) {
                        f.content = field.value
                    }
                })
            })
        }
    }
</script>
<style lang="css">
</style>