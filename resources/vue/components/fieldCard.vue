<template>
    <fieldset class="field-card">
          <input type="text" v-model="fieldName" placeholder="Enter Field Name" class="field-card__name" @change="nameField"/>

          <label for="fieldRequired">Required?</label>
          <input type="checkbox" name="required" id="fieldRequired" @change="requireField" v-model="fieldRequired">

          <div class="field-card__meta">
              <span>Type: {{ field.type }}</span>
          </div>
          <span @click="deleteField" v-if="deletable">Delete Field</span>
    </fieldset>
</template>
<script>
    import Bus from '../../js/admin.js'

    export default {
        name: 'inputField',
        data () {
            return {
                fieldName: '',
                fieldRequired: this.field.required,
                fieldType: this.field.type
            }
        },
        props: ['field', 'deletable'],
        methods: {
            deleteField () {
                Bus.$emit('deleteField', this.field)
            },
            requireField () {
                Bus.$emit('requireField', { field: this.field, required: this.fieldRequired })
            },
            nameField () {
                Bus.$emit('nameField', { field: this.field, name: this.fieldName })
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