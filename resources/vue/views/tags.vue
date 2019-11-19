<template>
    <div class="page">
        <div class="l-row l-max--960">
            <h1>Tags</h1>
            <button class="btn btn-primary btn-small">Create New</button>
        </div>
        <div class="cards">
            <div class="card" v-for="tag in tags">
                <div class="card__details">
                    <h3>{{ tag.tag_name }}</h3>
                </div>
                <div class="card__utilities">
                    <button class="delete" @click="deleteTag(tag.tag_id)">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'

export default {
    name: 'viewTags',
    data() {
        return {
            tags: null
        }
    },
    props: [],
    methods: {
        deleteTag(tagId) {
            axios.delete(`/tag?tag_id=${tagId}`)
                .then((res) => {
                    this.tags = res.data.tags
                })
        }
    },
    beforeCreate() {
        axios.get(`/tag`).then((res) => {
            this.tags = res.data
        })
    }
}

</script>
<style lang="css">
</style>
