<template>
    <div class="growler" :class="mode">
        <p>{{ message }}</p>
    </div>
</template>
<script>
    export default {
        name: '',
        data () {
            return {

            }
        },
        props: [
            'message',
            'mode'
        ],
        methods: {

        }
    }
</script>
<style lang="scss">

</style>