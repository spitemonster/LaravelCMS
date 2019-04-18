<template>
    <div>
        <h1>View Pages</h1>
        <h3 class="page" v-for="page, k in pages" :key="k">{{ page.title }} <router-link tag="span" :to="'/admin/page/' + page.page_id + '/edit'"><a>Edit Page</a></router-link><span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(page.page_id)">Delete Page</button>
            <h4 class="child" v-for="child, l in page.children"> {{ child.title }} <router-link tag="span" :to="'/admin/page/' + child.page_id + '/edit'"><a>Edit Page</a></router-link><span><a :href="child.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(child.page_id)">Delete Page</button></h4>
        </h3>
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
                // make the delete request and then refresh this.pages to reflect the new list of pages, post-delete.
                // probably a better way to do this but this works for now.
                axios.delete(`/page?page_id=${pageId}`)
                    .then((res) => {

                        axios.get('/page')
                            .then((response) => {
                                console.log(response.data)
                                this.pages = response.data

                                let growlerData = {
                                    mode: 'success',
                                    message: 'Page successfully deleted'
                                }
                                Bus.$emit('growl', growlerData)
                            })
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
.child {
    margin-left: 2rem;
    position: relative;
}

.child::before {
    content: "˪";
    position: relative;
    left: 0;
    /*font-size: 32px;*/
}
</style>