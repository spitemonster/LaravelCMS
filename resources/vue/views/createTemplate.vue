<template>
    <section>
        <fieldset>
                <input type="text" name="templateName" id="templateName" style="display: block;" required>
                <label for="templateName">Template Name</label>
        </fieldset>

        <div class="add-field">
            <div class="select-wrap">
                <select name="field-type" id="fieldSelector">
                    <option value="text">Text</option>
                    <option value="wysiwyg">WYSIWYG</option>
                    <option value="media">Media</option>
                </select>
            </div>

            <button @click="addField" class="btn btn-small">Add Field</button>
        </div>

        <fieldCard :field="field" v-for="field, i in fields" :key="i" :deletable="true" :index="i"></fieldCard>
        <button @click="saveTemplate" class="btn btn-primary btn--no-margin">Save Template</button>
    </section>
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
                this.fields.push({name: '', required: false, type: sel.value, index: this.fields.length});
            },
            saveTemplate () {
                let template = this.collectTemplateData()

                Bus.$emit('createTemplate', template)
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
                console.log(targetField);
                // this.fields.forEach((field) => {
                //     if (field === targetField.field) {
                //         field.required = targetField.required;
                //     }
                // })
            })

            Bus.$on('nameField', (targetField) => {
                this.fields.forEach((field) => {
                    console.log(field);
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