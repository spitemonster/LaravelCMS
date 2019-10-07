<template>
    <div class="page">
        <h1>Dashboard</h1>
        <h3>Welcome</h3>
        <div>
            <p v-for="page in pages">{{ page.title }} - Views: {{page.views}}</p>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
export default {
    data() {
        return {
            user: {},
            pages: []
        }
    },
    props: [],
    methods: {},
    created() {

    },
    mounted() {
        axios.get('/page')
            .then((res) => {
                this.pages = res.data;
            })
    }
}

</script>
<style lang="css">
</style>
