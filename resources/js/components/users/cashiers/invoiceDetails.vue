<template>
    <div>
        <div v-if="this.$store.state.user!=null">
            <p class="textLabel">ID: {{ invoice.id }} </p>
            <p class="textLabel">State: {{ invoice.state }} </p>
            <p class="textLabel">Meal id: {{ invoice.meal_id }} </p>
            <p class="textLabel">Date: {{ invoice.date }} </p>
            <p class="textLabel">Total price: {{ invoice.total_price }} </p>
            <p class="textLabel">Responsible Waiter Id: {{ invoice.responsible_waiter_id }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Name:  {{ invoice.waiterName }}</p>

            <label>Items:</label>
            <items-list :items="itemsInvoice"> </items-list>

        </div>
    </div>
</template>

<script>
    /*jshint esversion: 6 */
    import itemsList from './invoiceItemsList.vue';

    export default {
        props: ['invoice'],
        data:
            function() {
                return {
                    itemsInvoice: [],
                };
            },
        methods: {
            getInvoiceItems: function() {
                axios.get('api/invoiceItems/items/' + this.invoice.id)
                    .then(response=>{
                        this.itemsInvoice = response.data.data;
                    });
            },
        },
        mounted() {
            this.getInvoiceItems();
        },
        components: {
            itemsList,
        },

    };

</script>

<style scoped>
    p.textLabel {
        font-weight: bold;
    }

</style>