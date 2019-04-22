<template>
    <div>
        <h1>Edit Page</h1>
        <input type="text" id="pageName" :value="title">
        <input type="text" id="pageUrl" :value="url">

        <label for="showInMenu">Show in Menu?</label>
        <input type="checkbox" name="menu" id="showInMenu" v-model="menu" />

        <inputField v-for="field in fields"
                        :fieldType="field.type"
                        :fieldId="field.field_id"
                        :fieldName="field.name"
                        :fieldRequired="field.required"
                        :key="field.field_id"
                        :content="field.value"></inputField>

        <button @click="savePage">Save Page</button>
    </div>
</template>
<script>
import axios from 'axios'
import inputField from '../components/inputField.vue'
import Bus from '../../js/admin.js'
import router from '../../js/admin.js'

export default {
    name: '',
    data () {
        return {
            title: '',
            url: '',
            menu: false,
            fields: [],
            revisions: [],
            fieldsValid: false
        }
    },
    props: [],
    computed: {
    },
    methods: {
        savePage () {
            let headers = { 'Content-Type': 'application/json' }
            let name = document.querySelector('#pageName')
            let url = document.querySelector('#pageUrl')

            if (!name.value || !url.value) {

                if (!name.value) {
                    name.classList.add('invalid')
                }

                if (!url.value) {
                    url.classList.add('invalid')
                }

                let growlerData = {
                    mode: 'error',
                    message: `Please fill in marked fields and try again.`
                }

                return Bus.$emit('growl', growlerData);
            }

            let pageData = {}

            pageData.title = name.value
            pageData.url = url.value.toLowerCase()
            pageData.menu = document.querySelector('#showInMenu').value === 'on' ? true : false;

            pageData.fields = []

            //
            for (let i = 0; i < this.fields.length; i++) {
                let field = this.fields[i];

                let f = {};

                if (!field.content && field.required) {
                    this.invalidField(field.field_id);
                    break
                }

                f.content = field.value;
                f.field_id = field.field_id;
                f.field_name = field.name;
                f.type = field.type;

                pageData.fields.push(f)
            }

            this.fieldsValid = true;

            if (this.fieldsValid) {
                axios.patch(`/page?page_id=${this.$route.params.page_id}`, pageData, headers)
                .then((res) => {
                    let growlerData = {
                        mode: res.data.status,
                        message: res.data.message
                    }

                    Bus.$emit('growl', growlerData)
                })
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
    beforeCreate() {
        axios.get(`/page?page_id=${this.$route.params.page_id}`)
            .then((res) => {
                this.fields = res.data.values
                this.title = res.data.title
                this.url = res.data.url
                this.revisions = res.data.revisions
                this.menu = res.data.menu
            })
    },
    mounted() {

        document.addEventListener('keydown', (e) => {
            if (e.metaKey && e.which == 83) {
                e.preventDefault()

                this.savePage()
            }
        })

        Bus.$on('fieldFill', (field) => {
            let targetField = field.dataset.fieldid

            this.fields.forEach((f) => {
                if (f.field_id == targetField) {
                    f.value = field.value
                }
            })
        })
    }
}
</script>
<style lang="css">
</style>