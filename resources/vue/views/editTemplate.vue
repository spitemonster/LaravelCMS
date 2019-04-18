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
        <fieldCard v-for="field in fields" :key="field.id" :field="field" :deletable="false"></fieldCard>
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
                templateData.fields = JSON.stringify(this.fields);

                axios.patch(`/template?template_id=${this.$route.params.template_id}`, templateData, headers)
                    .then((res) => {
                        let growlerData = {
                            mode: 'success',
                            message: 'Template successfully updated'
                        }
                        Bus.$emit('growl', growlerData)
                    })

            },
            generateId () {
                return uuidv4()
            },
            addField () {
                let sel = document.getElementById('fieldSelector')

                this.fields.push({field_id: this.generateId(), name: '', type: sel.value, required: false})
            },
            removeField (targetId) {
                this.fields = this.fields.filter((field) => {
                    return field.field_id !== targetId
                })

                axios.delete(`/field?field_id=${targetId}`)
                    .then((res) => {
                        console.log(res)
                    })
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

            Bus.$on('delete', (fieldId) => {
                this.removeField(fieldId)
            })

            Bus.$on('nameField', (f) => {
                this.fields.forEach((field) => {
                    console.log('looking')
                    if (f.field_id === field.field_id) {
                        console.log('found it')
                        return field.name = f.name
                    }
                })
            })

            Bus.$on('requireField', (f) => {
                this.fields.forEach((field) => {
                    if (f.id === field.id) {
                        field.required = f.required
                    }
                })
            })
        }
    }
</script>
<style lang="css">
</style>