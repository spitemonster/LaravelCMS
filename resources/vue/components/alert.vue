<template>
    <div class="alert">
        <template v-if="alertData.type === 'delete'">
            <p>This will delete the page, its history and all its contents permanently.</p>
            <h3>Are you sure?</h3>
            <div>
                <button @click="deletePage()" class="delete">Delete</button>
                <button @click="closeAlert()">Cancel</button>
            </div>
        </template>
    </div>
</template>
<script>
import Bus from '../../js/admin.js'

export default {
    name: 'alert',
    data() {
        return {

        }
    },
    props: [
        'alertData'
    ],
    methods: {
        deletePage() {
            let pageID = this.alertData.page.page_id
            Bus.$emit('deletePage', pageID)

            this.closeAlert();
        },
        closeAlert() {
            this.$el.classList.remove('show');
        }
    },
    updated() {

    }
}

</script>
<style lang="css">
</style>
