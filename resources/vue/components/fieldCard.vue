<template>
    <div class="field-card">
        <fieldset>
            <input type="text" id="fieldName" v-model="fieldName" class="field-card__name" @change="nameField" required />
            <label for="fieldName">Field Name</label>
        </fieldset>
        <fieldset>
            <input type="checkbox" name="required" :id="index" @change="requireField" v-model="fieldRequired" required>
            <label :for="index">Required?</label>
        </fieldset>
        <div class="field-card__meta">
            <span>Type: {{ field.type }}</span>
            <button @click="alertDelete(field)" class="delete" v-if="deletable">Delete Field</button>
        </div>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'

export default {
    name: 'inputField',
    data() {
        return {
            fieldName: '',
            fieldRequired: this.field.required,
            fieldType: this.field.type
        }
    },
    props: ['field', 'deletable', 'index', 'pageCount'],
    methods: {
        deleteField() {
            Bus.$emit('deleteField', this.field)
        },
        requireField() {
            Bus.$emit('requireField', { field: this.field, required: this.fieldRequired })
        },
        nameField() {
            Bus.$emit('nameField', { field: this.field, name: this.fieldName })
        },
        alertDelete(field) {
            let fieldData = {
                type: 'field',
                id: field.field_id,
                msg: 'WARNING: This will delete this field from the template permanently. Any pages referencing this field may experience errors.'
            }

            Bus.$emit('alertDelete', fieldData)
        }
    },
    mounted() {
        this.fieldName = this.field.name ? this.field.name : ''
        this.fieldRequired = this.field.required
    }
}

</script>
<style lang="scss">
</style>
