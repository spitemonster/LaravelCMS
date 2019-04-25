<template>
  <div>
    <h1>View Templates <router-link tag="span" to="/admin/create/template"><a>Add New</a></router-link></h1>
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
                Bus.$emit('deleteTemplate', templateId);
            }
        },
        beforeCreate () {
            axios.get('/template')
              .then((res) => {
                    this.templates = res.data
              })
        },
        mounted() {
            Bus.$on('templateDeleted', () => {
                axios.get('/template')
                .then((res) => {
                    this.templates = res.data
                })
            });
        }
    }
</script>
<style lang="css">
</style>