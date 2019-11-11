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
                    <fieldset>
                        <input type="text" id="tags" v-model="tags" required />
                        <label for="tags">Tags (Comma Separated)</label>
                    </fieldset>
                    <button @click="savePage" class="btn btn-primary btn--no-margin">Save Page</button>
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
            tags: ''
        }
    },
    props: [],
    computed: {},
    methods: {
        savePage() {
            // set variables to confirm fields are filled
            let name = document.querySelector('#pageName')
            let url = document.querySelector('#pageUrl')
            let selected = document.querySelectorAll('.selected-image')
            let description = document.querySelector('#pageDescription')
            let tags = document.querySelector('#tags');
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
            pageData.tags = tags.value;
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

            if (this.fieldsValid) {
                Bus.$emit('updatePage', pageData)
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
                for (let i = 0; i < res.data.tags.length; i++) {
                    let tag = res.data.tags[i];

                    if (i === 0) {
                        tags = `${tags}${tag.name}`
                    } else {
                        tags = `${tags}, ${tag.name}`
                    }
                }
                this.tags = tags;

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
