<template>

    <div>
        <div class="jumbotron">
            <h1>Invoices</h1>
        </div>

        <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

        <div class="inline-buttons">
            <a v-if="showingPending" class="btn btn-outline-info" v-on:click.prevent="showPaid">Paid Invoices</a>

            <a v-if="!showingPending" class="btn btn-outline-info" v-on:click.prevent="showPending">Pending Invoices</a>
        </div>


        <div v-if="showingPending">
            <div v-if="showingPending" class="jumbotron">
                <label> <h4>Pending Invoices: </h4> </label>
                <pending-invoices-list :invoices="pendingInvoices" :showSelected="false" v-on:show-details="showDetails" v-on:pay-invoice="showModalToPay"> </pending-invoices-list>
            </div>

            <div class="jumbotron" v-if="showingDetails">
                <label><h4> Details of invoice: </h4></label>
                <invoice-details :invoice="invoice"> </invoice-details>
            </div>
        </div>

        <div v-if="!showingPending">
            <div v-if="!showingPending" class="jumbotron">
                <label> <h4>Paid Invoices: </h4> </label>
                <paid-invoices-list :showSelected="false" v-on:show-details="showDetails"> </paid-invoices-list>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="payInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Pay Invoice</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" v-if="paying">
                        <error-validation :showErrors="showErrors" :errors="errors" @close="close"></error-validation>

                        <p class="textLabel">ID: {{ invoicePay.id }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Meal id: {{ invoicePay.meal_id }}</p>
                        <p class="textLabel">Responsible Waiter Id: {{ invoicePay.responsible_waiter_id }} &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Name:  {{ invoicePay.waiterName }}</p>
                        <p class="textLabel">Date: {{ invoicePay.date }} </p>
                        <p class="textLabel">Total price: {{ invoicePay.total_price }} </p>

                        <div class="form-group">
                            <label for="inputNif">NIF</label>
                            <input type="text" class="form-control" name="nif" id="inputNif" placeholder="Client NIF" v-model="clientNif"/>

                            <label for="inputName">Name</label>
                            <input type="text" class="form-control" name="name" id="inputName" placeholder="Client name" v-model="clientName"/>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="payInvoice">Pay</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script>
    /*jshint esversion: 6 */
    import pendingInvoicesList from './cashiers/listPendingInvoices.vue';
    import paidInvoicesList from './cashiers/listPaidInvoices.vue';
    import invoiceDetails from './cashiers/invoiceDetails.vue';
    import errorValidation from '../helpers/showErrors.vue';
    import showMessage from '../helpers/showMessage.vue';

    export default {
        data:
        function() {
            return {
                showMessage: false,
                pendingInvoices: [],
                paidInvoices: [],
                errors: [],
                message: "",
                showErrors: false,
                typeofmsg: "",
                clientNif: "",
                clientName: "",
                showingDetails: false,
                invoice: null,
                invoicePay: null,
                paying: false,
                showingPending: true,
            };
        },
        methods: {
         getPendingInvoices: function() {
            axios.get('api/invoices/pending')
            .then(response=>{
                this.pendingInvoices = response.data.data;
            });
        },
        showDetails: function(invoiceDetails) {
         this.showingDetails = true;
         this.invoice = invoiceDetails;
     },
     showPaid: function() {
        this.showingPending = false;
    },
    showPending: function() {
        this.showingPending = true;
        this.getPendingInvoices();
    },
    showModalToPay: function(invoice) {
        this.showErrors=false;
        this.clientNif = "";
        this.clientName = "";
        $('#payInvoiceModal').modal('toggle');
        this.paying = true;
        this.invoicePay = invoice;
    },
    payInvoice: function() {

        axios.post('api/invoices/pay/' + this.invoicePay.id, {
            nif: this.clientNif,
            name: this.clientName,
        }).then(response => {
            $('#payInvoiceModal').modal('hide');
            this.$socket.emit("invoicePaid", this.$store.state.user, response.data.data);
            this.getPendingInvoices();
            this.clientNif = "";
            this.clientName = "";
            this.showMessage = true;
            this.typeofmsg= "alert-success";
            this.message = "Invoice paid with success.";
        }).catch(error => {
            console.log(error);
            if(error.response.status == 422) {
                this.showErrors=true;
                this.showMessage=false;
                this.typeofmsg= "alert-danger";
                this.showMessage = false;
                this.errors=error.response.data.errors;
            }
        });
    },
    close(){
        this.showErrors=false;
        this.showMessage=false;
    },
},
mounted() {
 this.getPendingInvoices();
},
components: {
    'pending-invoices-list': pendingInvoicesList,
    'invoice-details': invoiceDetails,
    'paid-invoices-list': paidInvoicesList,
    'show-message':showMessage,
    'error-validation':errorValidation,
},
sockets: {
    new_pending_invoice() {
        this.getPendingInvoices();
    },
}

};
</script>

<style scoped>
p.textLabel {
    font-weight: bold;
}

</style>