<template>
    <div class="page">
        <div class="img-grid">
            <div class="img-grid__image" v-for="m in media">
                <img :src="m.url " alt="">
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'

export default {
    name: 'viewMedia',
    data() {
        return {
            media: [],
        }
    },
    props: [],
    methods: {

    },
    mounted() {
        axios.get('/media')
            .then((res) => {

                res.data.forEach((item) => {
                    this.media.push(item);
                })
            })
    }
}

</script>
<style lang="css">
</style>
