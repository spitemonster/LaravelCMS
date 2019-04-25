<template>
    <div>
        <h1>View Pages</h1>
        <h3 class="page" v-for="page, k in pages" :key="k">{{ page.title }} <router-link tag="span" :to="'/admin/page/' + page.page_id + '/edit'"><a>Edit Page</a></router-link><span><a :href="page.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(page.page_id)">Delete Page</button>
            <!-- <details v-if="page.children"> -->
                <!-- <summary>View Children</summary> -->
                <h4 class="child" v-for="child, l in page.children"> {{ child.title }} <router-link tag="span" :to="'/admin/page/' + child.page_id + '/edit'"><a>Edit Page</a></router-link><span><a :href="child.url" target="_blank" rel="noopener noreferrer">View Page</a></span> <button @click="deletePage(child.page_id)">Delete Page</button></h4>
            <!-- </details> -->
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
                Bus.$emit('deletePage', pageId);
            }
        },
        beforeCreate () {
            axios.get('/page')
                .then((res) => {
                    this.pages = res.data
                })
        },
        mounted() {
            Bus.$on('pageDeleted', () => {
                axios.get('/page')
                    .then((res) => {
                        this.pages = res.data
                    })
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