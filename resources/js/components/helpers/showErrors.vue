<template>
    <div class="alert alert-danger" v-if="showErrors">             
        <button type="button" class="close" @click="close">&times;</button>
        <ul>
            <li v-for="(value, key, index) in validationErrors">{{ value }}</li>
        </ul>
    </div>
</template>
<script>
    /*jshint esversion: 6 */

    export default{
        props: ['errors','showErrors'],
        computed: {
            validationErrors(){
                let errors = Object.values(this.errors);
                errors = errors.flat();
                return errors;
            }
        },
        methods:{
            close(){
                this.$emit('close');
            },
        },
    };
</script>