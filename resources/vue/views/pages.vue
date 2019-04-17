<template>
    <div>
        <h1>View Pages</h1>
        <h3 v-for="page, k in pages" :key="k">{{ page.title }} <router-link tag="span" :to="'/admin/page/' + page.page_id + '/edit'"><a>Edit Page</a></router-link><span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(page.page_id)">Delete Page</button></h3>
    </div>
</template>
<script>
    import axios from 'axios'
    import router from '../../js/admin.js'
    import Bus from '../../js/admin.js'

    export default {
        data () {
            return {
                pages: {}
            }
        },
        props: [],
        methods: {
            deletePage(pageId) {

                // console.log(pageId)
                this.pages = this.pages.filter((page) => {
                    return page.page_id !== pageId
                })


                axios.delete(`/page?page_id=${pageId}`)
                    .then((res) => {
                        let growlerData = {
                            mode: 'success',
                            message: 'Page successfully deleted'
                        }
                        Bus.$emit('growl', growlerData)
                    })
            }
        },
        beforeCreate () {
            axios.get('/page')
                .then((res) => {
                    this.pages = res.data
                })
        }
    }
</script>
<style lang="css">
</style>