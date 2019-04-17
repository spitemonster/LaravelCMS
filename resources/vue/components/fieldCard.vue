<template>
    <fieldset class="field-card">
          <input type="text" v-model="fieldName" placeholder="Enter Field Name" class="field-card__name" @change="nameField"/>
          <div class="field-card__meta">
              <span>Type: {{ field.type }}</span>
              <span>ID: {{ field.field_id }}</span>
          </div>
          <span @click="deleteField">Delete Field</span>
    </fieldset>
</template>
<script>
    import Bus from '../../js/admin.js'

    export default {
        name: 'inputField',
        data () {
            return {
                fieldName: this.field.name ? this.field.name : '',
                fieldId: this.field.field_id,
                fieldRequired: this.field.required ? this.field.required : false,
                fieldType: this.field.text
            }
        },
        props: ['field'],
        methods: {
            deleteField () {
                Bus.$emit('delete', this.fieldId)
            },
            requireField () {
                Bus.$emit('requireField', { id: this.fieldId, required: this.fieldRequired })
            },
            nameField () {
                Bus.$emit('nameField', { field_id: this.fieldId, name: this.fieldName })
            }
        }
    }
</script>
<style lang="scss">
</style>