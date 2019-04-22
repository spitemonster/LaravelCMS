<template>
    <main>
        <input type="text" name="templateName" placeholder="Template Name" id="templateName" style="display: block;">

        <select name="field-type" id="fieldSelector">
            <option value="text">Text</option>
            <option value="textarea">Text Area</option>
            <option value="wysiwyg">WYSIWYG</option>
            <option value="postIndex">Post Index</option>
        </select>

        <button @click="addField">Add Field</button>
            <fieldCard :field="field" v-for="field in fields" :key="field.name" :deletable="true"></fieldCard>
        <button @click="saveTemplate">Save Template</button>
    </main>
</template>
<script>
    import fieldCard from '../components/fieldCard.vue'
    import Bus from '../../js/admin.js'
    import axios from 'axios'

    export default {
        data () {
            return {
                fields: []
            }
        },
        methods: {
            addField () {
                let sel = document.querySelector('#fieldSelector')
                this.fields.push({name: '', required: false, type: sel.value});
            },
            saveTemplate () {
                let template = this.collectTemplateData()

                let headers = { 'Content-Type': 'application/json' }

                axios.post('/template', template, headers)
                    .then((res) => {
                        this.$router.push({ name: 'viewTemplates' })

                        let growlerData = {
                            mode: res.data.status,
                            message: res.data.message
                        }
                        Bus.$emit('growl', growlerData)
                    })
            },
            collectTemplateData() {
                let template_name = document.querySelector('#templateName').value
                let template_fields = this.fields

                let template = {
                    template_name: template_name,
                    template_fields: template_fields
                }

                return template;
            }
        },
        components: {
            fieldCard
        },
        mounted () {
            // command/control + s save the template
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
                        field.required = targetField.required;
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
        },
        updated () {
        }
    }
</script>
<style lang="css">

</style>