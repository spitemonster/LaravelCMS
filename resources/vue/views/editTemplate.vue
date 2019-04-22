<template>
    <div>
        <h2>Edit Template</h2>
        <select name="field-type" id="fieldSelector">
          <option value="text">Text</option>
          <option value="textarea">Text Area</option>
          <option value="wysiwyg">WYSIWYG</option>
          <option value="postIndex">Post Index</option>
        </select>

        <button @click="addField">Add Field</button>
        <input type="text" name="templateName" placeholder="Template Name" id="template-name" style="display: block;" v-model="templateName">
            <fieldCard :field="field" v-for="field in fields" :key="field.name" :deletable="false"></fieldCard>
        <button @click="saveTemplate">Save Template</button>
    </div>
</template>
<script>
    import axios from 'axios'
    import fieldCard from '../components/fieldCard.vue'
    import Bus from '../../js/admin.js'
    import uuidv4 from 'uuid/v4'

    export default {
        data () {
            return {
                templateName :'',
                fields: []
            }
        },
        props: [],
        methods: {
            saveTemplate () {
                let templateData = {}
                let headers = { 'Content-Type': 'application/json' }

                templateData.name = this.templateName
                templateData.fields = this.fields;

                axios.patch(`/template?template_id=${this.$route.params.template_id}`, templateData, headers)
                    .then((res) => {
                        this.$router.push({ name: 'viewTemplates' })
                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }

                        Bus.$emit('growl', growlerData)
                    })

            },
            addField () {
                let sel = document.querySelector('#fieldSelector')
                this.fields.push({name: '', required: false, type: sel.value});
            },
        },
        components: {
            fieldCard,
        },
        beforeCreate() {
            axios.get(`/template/?template_id=${this.$route.params.template_id}`)
                .then((res) => {
                    this.fields = res.data.fields
                    this.templateName = res.data.name
                })
        },
        mounted() {
            document.addEventListener('keydown', (e) => {
                if (e.metaKey && e.which == 83) {
                    e.preventDefault()

                    this.saveTemplate()
                }
            })

            Bus.$on('deleteField', (targetField) => {
                this.fields = this.fields.filter((field) => {
                    return field !== targetField;
                })
            })

            Bus.$on('requireField', (targetField) => {
                this.fields.forEach((field) => {
                    if (field === targetField.field) {
                        field.required = targetField.required
                    }
                })
            })

            Bus.$on('nameField', (targetField) => {
                this.fields.forEach((field) => {
                    if (field === targetField.field) {
                        field.name = targetField.name;
                    }
                })

            })
        }
    }
</script>
<style lang="css">
</style>