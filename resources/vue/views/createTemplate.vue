<template>
    <main>
      <input type="text" name="templateName" placeholder="Template Name" id="template-name" style="display: block;">

      <select name="field-type" id="fieldSelector">
          <option value="text">Text</option>
          <option value="textarea">Text Area</option>
          <option value="wysiwyg">WYSIWYG</option>
          <option value="postIndex">Post Index</option>
      </select>

      <button @click="addField">Add Field</button>
      <draggable element="form" :list="fields" id="test">
            <fieldCard :field="field" v-for="field in fields" :key="field.id" :deletable="true"></fieldCard>
      </draggable>
      <button @click="saveTemplate">Save Template</button>
    </main>
</template>
<script>
    import fieldCard from '../components/fieldCard.vue'
    import draggable from 'vuedraggable'
    import Bus from '../../js/admin.js'
    import axios from 'axios'
    import uuidv4 from 'uuid/v4'

    export default {
        data () {
            return {
                fields: []
            }
        },
        methods: {
            generateId () {
                return uuidv4()
            },
            addField () {
                let sel = document.getElementById('fieldSelector')
                this.fields.push({field_id: this.generateId(), name: '', type: sel.value, required: false});
            },
            removeField (targetId) {
                this.fields = this.fields.filter((field) => {
                    return field.field_id !== targetId
                })
            },
            saveTemplate () {
                let templateName = document.querySelector('#template-name').value

                let headers = { 'Content-Type': 'application/json' }

                let template = {
                    name: templateName,
                    template_id: this.generateId(),
                    fields: JSON.stringify(this.fields)
                }

                axios.post('/template', template, headers)
                    .then((res) => {

                        let growlerData = {
                            mode: 'success',
                            message: 'Template successfully created'
                        }
                        Bus.$emit('growl', growlerData)
                    })
            }
        },
        components: {
            fieldCard,
            draggable
        },
        mounted () {
            document.addEventListener('keydown', (e) => {
                if (e.metaKey && e.which == 83) {
                    e.preventDefault()

                    this.saveTemplate()
                }
            })

            Bus.$on('delete', (fieldId) => {
                this.removeField(fieldId)
            })

            Bus.$on('requireField', (field) => {
                this.fields.filter((f) => {
                    if (f.id === field.id) {
                        f.required = !f.required
                    }
                })
            })

            Bus.$on('nameField', (field) => {
                this.fields.forEach((f) => {
                    if (f.field_id === field.field_id) {
                        f.name = field.name;
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