<template>
    <div class="alert">
        <template v-if="alertData.type === 'deletePage'">
            <div class="alert__warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z" /></svg>
                <div>
                </div>
            </div>
            <p class="alert__warning-message">This will delete the page, its history and all its contents permanently.</p>
            <h3>Are you sure?</h3>
            <div class="alert__buttons">
                <button @click="deletePage()" class="delete">Delete</button>
                <button @click="closeAlert()" class="affirm">Cancel</button>
            </div>
        </template>
        <template v-else-if="alertData.type === 'deleteUser'">
            <div class="alert__warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z" /></svg>
                <div>
                </div>
            </div>
            <p class="alert__warning-message">This will delete the user, its history and all its contents permanently.</p>
            <h3>Are you sure?</h3>
            <div class="alert__buttons">
                <button @click="deleteUser()" class="delete">Delete</button>
                <button @click="closeAlert()" class="affirm">Cancel</button>
            </div>
        </template>
        <template v-else-if="alertData.type === 'deleteField'">
            <div class="alert__warning">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.31 7.526c-.099-.807.528-1.526 1.348-1.526.771 0 1.377.676 1.28 1.451l-.757 6.053c-.035.283-.276.496-.561.496s-.526-.213-.562-.496l-.748-5.978zm1.31 10.724c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z" /></svg>
                <div>
                </div>
            </div>
            <p class="alert__warning-message">WARNING: There are currently {{ alertData.pageCount }} pages using this template. This will delete the field and its contents for every page that uses it permanently. This may effect the display of pages using this template.</p>
            <h3>Are you sure?</h3>
            <div class="alert__buttons">
                <button @click="deleteField()" class="delete">Delete</button>
                <button @click="closeAlert()" class="affirm">Cancel</button>
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
        deleteUser() {
            let userID = this.alertData.user.user_id
            Bus.$emit('deleteUser', userID)

            this.closeAlert();
        },
        deleteField() {
            let fieldID = this.alertData.field

            Bus.$emit('deleteField', fieldID)

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
