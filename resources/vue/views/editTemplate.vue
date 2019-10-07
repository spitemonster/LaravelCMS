<template>
    <div class="page">
        <h2>Edit Template</h2>
        <fieldset>
            <input type="text" name="templateName" id="templateName" style="display: block;" v-model:value="templateName" required>
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
        <fieldCard :field="field" v-for="field, i in fields" :key="field.name" :deletable="true" :index="i"></fieldCard>
        <button @click="saveTemplate" class="btn btn-primary btn--no-margin">Save Template</button>
    </div>
</template>
<script>
import axios from 'axios'
import fieldCard from '../components/fieldCard.vue'
import Bus from '../../js/admin.js'
import uuidv4 from 'uuid/v4'

export default {
    data() {
        return {
            templateName: '',
            fields: [],
            deleteFields: []
        }
    },
    props: [],
    methods: {
        saveTemplate() {
            let templateData = {}
            let headers = { 'Content-Type': 'application/json' }

            templateData.template_id = this.$route.params.template_id;
            templateData.name = this.templateName
            templateData.fields = this.fields;
            templateData.deleteFields = this.deleteFields;

            Bus.$emit('updateTemplate', templateData)
        },
        addField() {
            let sel = document.querySelector('#fieldSelector')
            this.fields.push({ name: '', required: false, type: sel.value, field_id: null });
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

                console.log(this.fields);
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

            this.deleteFields.push(targetField.field_id);
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
