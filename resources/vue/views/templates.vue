<template>
  <div>
    <h1>View Templates</h1>
    <h3 v-for="template, k in templates"> {{ template.name }} <button @click="deleteTemplate(template.template_id)">Delete</button> <router-link tag="span" :to="'/admin/template/' + template.template_id + '/edit'"><a>Edit Template</a></router-link></h3>
  </div>
</template>
<script>
    import fieldCard from '../components/fieldCard.vue'
    import draggable from 'vuedraggable'
    import Bus from '../../js/admin.js'
    import uuidv4 from 'uuid/v4'
    import axios from 'axios'

    export default {
        data () {
            return {
                templates: {}
            }
        },
        props: [],
        methods: {
            deleteTemplate(templateId) {
                axios.delete(`/template?template_id=${templateId}`)
                    .then((res) => {
                        let growlerData = {
                            mode: 'success',
                            message: 'Template successfully deleted'
                        }

                        this.templates = this.templates.filter((template) => {
                            return template.template_id !== templateId;
                        })

                        Bus.$emit('growl', growlerData)
                    })
                    .catch((err) => {
                        let growlerData = {
                            mode: 'alert',
                            message: err.response.data
                        }

                        Bus.$emit('growl', growlerData)
                    })
            }
        },
        beforeCreate () {
            axios.get('/template')
              .then((res) => {
                    this.templates = res.data
              })
        }
    }
</script>
<style lang="css">
</style>