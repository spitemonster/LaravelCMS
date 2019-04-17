<template>
    <div>
        <input type="text" name="title" v-model="title" placeholder="title" />
        <input type="text" name="url" pattern="[-/_a-zA-Z0-9]+" :value="url" placeholder="url" />
        <inputField fieldType="wysiwyg" fieldName="Content" fieldRequired="true"></inputField>
        <button @click="savePost">Save Post</button>
    </div>
</template>
<script>
    import inputField from '../components/inputField.vue'
    import Bus from '../../js/admin.js'
    import axios from 'axios'

    export default {
        name: '',
        components: {
            inputField
        },
        data () {
            return {
                title: '',
                content: ''
            }
        },
        props: [],
        methods: {
            savePost() {
                let postData = {}
                let headers = { 'Content-Type': 'application/json' }

                postData.title = this.title
                postData.url = this.url
                postData.content = this.content

                axios.post('/post', postData, headers)
                    .then((res) => {
                        console.log(res)
                    })


            }
        },
        computed: {
            url() {
                let wsReg = /[^-a-zA-Z0-9]+/g
                let url = this.title.replace(wsReg, '-').toLowerCase()

                if (url.split('')[0] !== '/') {
                    return `/${url}`
                }

                return url
            }
        },
        mounted() {
            Bus.$on('fieldFill', (data) => {
                this.content = data.value
            })
        }
    }
</script>
<style lang="css">
</style>