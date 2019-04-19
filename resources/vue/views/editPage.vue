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

        <select v-if="hasRevisions" name="revisions" @change="loadRevision">
            <option v-for="revision in revisions.slice().reverse()" :value="revision.id">{{ revision.name }} - {{ revision.createdAt }} - {{ revision.updatedBy }}</option>
        </select>

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
            revisions: []
        }
    },
    props: [],
    computed: {
    },
    methods: {
        savePage () {
            let headers = { 'Content-Type': 'application/json' }

            let pageData = {}
            pageData.title = document.querySelector('#pageName').value
            pageData.url = document.querySelector('#pageUrl').value
            pageData.menu = document.querySelector('#showInMenu').value === 'on' ? true : false;
            pageData.fields = []

            this.fields.forEach((field) => {
                let obj = {};

                obj.value = field.value;
                obj.field_id = field.field_id;
                obj.page_id = pageData.page_id;
                obj.field_name = field.field_name;
                obj.type = field.type;

                pageData.fields.push(obj);
            });

            pageData.fields = pageData.fields;

            axios.patch(`/page?page_id=${this.$route.params.page_id}`, pageData, headers)
            .then((res) => {
                let growlerData = {
                    mode: 'success',
                    message: 'Page successfully updated'
                }

                Bus.$emit('growl', growlerData)
            })
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