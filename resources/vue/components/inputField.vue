<template>
    <div>
        <label :for="fieldName">{{ fieldName }} <template v-if="fieldRequired">*</template></label>
        <template v-if="fieldType === 'text'">
            <input type="text" :name="fieldName" :data-fieldId="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err: invalid}" @input="fieldContent($event)" :value="content">
        </template>
        <template v-else-if="fieldType === 'textarea'">
            <textarea :name="fieldName" :data-fieldId="fieldId" :required="fieldRequired ? true : false" :id="fieldName" class="inputField" v-bind:class="{err : invalid}" @input="fieldContent($event)">{{ content }}</textarea>
        </template>
        <template v-else-if="fieldType === 'wysiwyg'">
            <form action="" method="get" accept-charset="utf-8">
                <input type="hidden" name="content" :value="content" :name="fieldName" :data-fieldId="fieldId" :required="fieldRequired ? true : false" id="x" class="inputField">
                <trix-editor input="x"></trix-editor>
            </form>
        </template>
    </div>
</template>
<script>
    import Bus from '../../js/admin.js'
    import Trix from 'trix'

    export default {
        data () {
            return {
                invalid: false
            }
        },
        props: ['fieldType', 'fieldName', 'fieldId', 'fieldRequired', 'content'],
        methods: {
            fieldContent (e) {
                Bus.$emit('fieldFill', e.target)
            }
        },
        mounted () {
            Trix.config.blockAttributes.default.tagName = "p"

            document.addEventListener('trix-change', () => {
                let content = document.querySelector('#x')
                Bus.$emit('fieldFill', content)
            })

            Bus.$on('invalidField', (fieldId) => {
                if (this.fieldId === fieldId) {
                    this.invalid = true;
                }
            })
        }
    }
</script>
<style lang="css">
.err {
    border: 2px solid red;
}
</style>