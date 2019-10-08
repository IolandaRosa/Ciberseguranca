<template>

    <div>
        <div v-if="this.$store.state.user != null">

            <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

            <vue-good-table ref="table"  :columns="columns" :rows="invoices" :pagination-options="{ enabled: true, perPage: 10}"
                            :search-options="{ enabled: true}" @on-row-click="onRowClick" :row-style-class="rowStyleFn">
                <template slot="table-row" slot-scope="props">

                    <span v-if="props.column.field=='actions'">
                        <span>
                            <span v-if="!isManager">
                                <button @click="showDetails(props.row)" class="btn btn-outline-info btn-xs"><i class="fas fa-eye">&nbsp;</i>Details</button>
                                <button @click="payInvoice(props.row)" class="btn btn-info btn-xs">Pay</button>
                            </span>
                            <span v-else>
                                <button @click="markInvoiceAsNotPaid(props.row)" class="btn btn-danger btn-xs">Mark as not paid</button>
                            </span>
                        </span>
                    </span>

                    <span v-else>
                        {{props.formattedRow[props.column.field]}}
                    </span>

                </template>
            </vue-good-table>
        </div>
    </div>

</template>

<script type="text/javascript">
    /*jshint esversion: 6 */
    import showMessage from '../../helpers/showMessage.vue';

    export default {
        props: ['invoices','isManagerDashboard'],
        data:
            function() {
                return {
                    showMessage:false,
                    message:'',
                    typeofmsg: "",
                    selectedRow: null,
                    selectedInvoice: null,
                    isManager: false,
                    columns: [
                        {
                            label: "Id",
                            field: 'id',
                        }, {
                            label: "Table_Number",
                            field: 'table_number',
                        },
                        {
                            label: 'State',
                            field: 'state',
                        }, {
                            label: 'Meal Id',
                            field: 'meal_id',
                        }, {
                            label: 'Date',
                            field: 'date',
                        }, {
                            label: 'Total Price',
                            field: 'total_price',
                        }, {
                            label: 'Responsible Waiter Id',
                            field: 'responsible_waiter_id',
                        }, {
                            label: 'Actions',
                            field: 'actions',
                            sortable: false,
                        }
                    ],
                };
            },
        methods:{
            payInvoice(row) {
                this.$emit("pay-invoice", row);
            },
            markInvoiceAsNotPaid(row) {
                this.$emit("invoice-not-paid", row);
            },
            showDetails(row) {
                this.$emit("show-details", row);
            },
            onRowClick(params){
                if(this.showSelected == true)
                {
                    this.selectedRow = params.row.originalIndex;
                }
            },rowStyleFn(row) {
                return this.selectedRow === row.originalIndex  && this.showSelected == true?'selectedRow':'';
            },

            close(){
            }
        },
        mounted(){

            if(this.isManagerDashboard != null)
            {
                this.isManager = this.isManagerDashboard;
            }
            this.$set(this.columns[0], 'hidden', this.isManager);
            this.$set(this.columns[1], 'hidden', !this.isManager);
            this.$set(this.columns[2], 'hidden', this.isManager);
            this.$set(this.columns[4], 'hidden', this.isManager);
        },
        components: {
            'show-message':showMessage,
        },
    };
</script>

<style >
    .selectedRow{
        background-color: darkgrey;
    }
</style>

