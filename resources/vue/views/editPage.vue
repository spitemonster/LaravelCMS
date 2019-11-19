<template>
    <div class="page">
        <template v-if="!pageLoaded">
            <p>Loading</p>
        </template>
        <template v-if="pageLoaded">
            <h1>Edit Page</h1>
            <input type="radio" name="tab" id="tabOne" checked>
            <input type="radio" name="tab" id="tabTwo">
            <input type="radio" name="tab" id="tabThree">
            <fieldset>
                <input type="text" id="pageName" :value="page.title" required />
                <label for="pageName">Page Name</label>
            </fieldset>
            <fieldset>
                <input type="text" id="pageUrl" :value="page.url" required />
                <label for="pageUrl">Page URL</label>
            </fieldset>
            <label for="tabOne" class="tab">Page Content</label>
            <label for="tabTwo" class="tab">Page Details</label>
            <label for="tabThree" class="tab">Page Analytics</label>
            <div class="content--wrapper">
                <div class="content" id="tabContentOne">
                    <inputField v-for="field in page.values" :fieldType="field.type" :fieldId="field.field_id" :fieldName="field.field_name" :fieldRequired="field.required" :key="field.field_id" :content="field.content"></inputField>
                    <div class="tags">
                        <fieldset v-for="tag in tags">
                            <label class="checkbox">{{ tag.tag_name }}<input type="checkbox" name="tags" value="tag.tag_id" :checked="pageTags.includes(tag.tag_id)" @change="updateTags(tag.tag_id)"><span class="checkbox__box"></span></label>
                        </fieldset>
                        <fieldset class="small">
                            <input type="text" id="newTag" name="newTag" class="small" required />
                            <label for="newTag">Create New </label>
                            <button class="btn btn-primary btn-tiny" @click="createNewTag">+</button>
                        </fieldset>
                    </div>
                    <div class="page-card__utilities">
                        <button @click="savePage" class="btn btn-primary btn--no-margin">Save Page</button>
                        <a :href="page.url" target="_blank">View Page</a>
                    </div>
                </div>
                <div class="content" id="tabContentTwo">
                    <p>Created By: {{ page.created_by ? page.created_by.name : 'User Deleted' }}</p>
                    <p v-if="page.updated_by">Last Updated By: {{ page.updated_by.name }}</p>
                    <label class="checkbox">Show in Menu?<input type="checkbox" v-model="page.menu" /><span class="checkbox__box"></span></label>
                    <label class="checkbox">Private Page?<input type="checkbox" v-model="page.private" /><span class="checkbox__box"></span></label>
                    <fieldset>
                        <textarea id="pageDescription" :value="page.description" required></textarea>
                        <label for="pageDescription">Meta Description</label>
                    </fieldset>
                </div>
                <div class="content" id="tabContentThree">
                    <p>Views - {{ page.views }}</p>
                </div>
            </div>
        </template>
    </div>
</template>
<script>
import axios from 'axios'
import inputField from '../components/inputField.vue'
import Bus from '../../js/admin.js'
import router from '../../js/admin.js'

export default {
    name: '',
    data() {
        return {
            pageLoaded: false,
            page: {},
            fieldsValid: false,
            tags: '',
            pageTags: [],
            selectedTags: [],
            removeTags: []
        }
    },
    props: [],
    computed: {},
    watch: {
        tagObj() {}
    },
    methods: {
        savePage() {
            // set variables to confirm fields are filled
            let name = document.querySelector('#pageName')
            let url = document.querySelector('#pageUrl')
            let selected = document.querySelectorAll('.selected-image')
            let description = document.querySelector('#pageDescription')
            // start pageData variable
            let pageData = {}

            selected.forEach((img) => {
                img.classList.remove('selected-image');
            })

            // check that the page title and url are set, otherwise growl
            if (!this.page.title || !this.page.url) {

                if (!this.page.title) {
                    name.classList.add('invalid')
                }

                if (!this.page.url) {
                    url.classList.add('invalid')
                }

                let growlerData = {
                    mode: 'error',
                    message: `Please fill in marked fields and try again.`
                }

                return Bus.$emit('growl', growlerData);
            }

            // get pageID from the route param. i'm operating under the assumption this is trustworthy.
            // furthermore...why would you fuck with the routes unless you were navigating to another page?
            pageData.page_id = this.$route.params.page_id
            pageData.title = name.value
            pageData.description = pageDescription.value
            // make sure whatever url a user enters gets switched to lowercase because this is not a farm and we are not farmers
            pageData.url = this.page.url.toLowerCase()
            pageData.menu = this.page.menu
            pageData.private = this.page.private
            pageData.tags = this.selectedTags;
            pageData.removeTags = this.removeTags;
            // set fields object
            pageData.fields = []

            // language here is a little tricky. field content is stored as 'values' in the database and returned as such
            // but it's easier to refer to them as 'fields' in this context
            // it makes sense to me.

            // used a for loop so that I could break out of it if a field is invalid, which is not doable with a foreach
            for (let i = 0; i < this.page.values.length; i++) {
                let field = this.page.values[i];

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

            // if it makes it through collecting fields, set fieldsValid to true
            this.fieldsValid = true;

            // just to double confirm, if fields valid is true, emit the updatePage event with pageData
            // and the event listener on App.vue takes care of everything else

            let data = {
                type: 'page',
                id: pageData.page_id,
                data: pageData,
                redirect: 'viewPages'
            }

            if (this.fieldsValid) {
                Bus.$emit('update', data)
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
        },
        createNewTag() {
            let newTag = document.querySelector('#newTag')

            if (newTag.value === '') {
                let growlerData = {
                    mode: 'error',
                    message: 'Tag name cannot be empty.'
                }

                return Bus.$emit('growl', growlerData);
            }

            axios.post(`/tag?tag_name=${newTag.value}`)
                .then((res) => {
                    let growlerData = {
                        mode: 'error',
                        message: res.data.message
                    }

                    if (res.data.status === 'success') {
                        growlerData.mode = 'success'
                        this.tags = res.data.tags;
                    }

                    newTag.value = null
                    newTag.setAttribute('valid', true)

                    Bus.$emit('growl', growlerData)
                })
        },
        updateTags(tagId) {
            if (this.selectedTags.includes(tagId)) {
                this.selectedTags = this.selectedTags.filter(tag => tag !== tagId)

                if (this.pageTags.includes(tagId)) {
                    this.removeTags.push(tagId)
                }
            } else {
                this.selectedTags.push(tagId)

                if (this.removeTags.includes(tagId)) {
                    this.removeTags = this.removeTags.filter(tag => tag !== tagId)
                }
            }

            console.log('remove tags ', this.removeTags)
        }
    },
    components: {
        inputField
    },
    beforeCreate() {

        // as the page is loading, request the page data
        // once it's loaded, set pageLoaded to true and Robert is your mother's uncle
        axios.get(`/page?page_id=${this.$route.params.page_id}`)
            .then((res) => {
                let tags = '';
                this.page = res.data
                this.fields = res.data.values
                this.pageLoaded = true

                res.data.tags.forEach((tag) => {
                    this.pageTags.push(tag.tag_id)
                })

                this.selectedTags = this.pageTags

                console.log(this.selectedTags)
            })

        axios.get(`/tag`)
            .then((res) => {
                this.tags = res.data
            })

    },
    mounted() {

        // sets CMD/CTRL+S as save
        document.addEventListener('keydown', (e) => {
            if (e.metaKey && e.which == 83) {
                e.preventDefault()
                this.savePage()
            }
        })

        // because of how I opted to do input fields, this is how data is filled
        // get the field, compare to the page's fields; once it finds a match update its content
        Bus.$on('fieldFill', (field) => {
            let targetField = field.dataset.fieldid
            this.page.values.forEach((f) => {

                if (f.field_id === targetField) {
                    f.content = field.value
                }
            })
        })
    }
}

</script>
<style lang="css">
</style>
