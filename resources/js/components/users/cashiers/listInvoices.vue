<template>

    <div>
        <div v-if="this.$store.state.user != null">

            <show-message :class="typeofmsg" :showSuccess="showMessage" :successMessage="message" @close="close"></show-message>

            <vue-good-table ref="table" mode="remote"  :columns="columns" :rows="rows" :pagination-options="{ enabled: true}"
            :search-options="{ enabled: true}" @on-row-click="onRowClick" :row-style-class="rowStyleFn"
            @on-page-change="onPageChange"
            @on-sort-change="onSortChange"
            @on-column-filter="onColumnFilter"
            @on-per-page-change="onPerPageChange"
            :totalRows="totalRecords">
            <template slot="table-row" slot-scope="props">

                <span v-if="props.column.field=='actions'">
                    <span>
                        <button @click="showDetails(props.row)" class="btn btn-outline-info btn-xs"><i class="fas fa-eye">&nbsp;</i>Details</button>
                        <span v-if="!isManager">
                            <button @click="payInvoice(props.row)" class="btn btn-info btn-xs">Pay</button>
                        </span>
                        <span v-if="isManager && props.row.state =='pending'">
                            <button @click="markInvoiceAsNotPaid(props.row)" class="btn btn-danger btn-xs">Mark as not paid</button>
                        </span>

                        <span v-if="isManager && props.row.state =='paid'">
                            <button @click="downloadPdf(props.row)" class="btn btn-info btn-xs">Download PDF</button>
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
                rows: [],
                totalRecords: 0,
                    // a map of column filters example: {name: 'john', age: '20'}
                    serverParams: {
                        columnFilters: {
                        },
                        sort: {
                            field: '',// example: 'name'
                            type: '',// 'asc' or 'desc'
                        },
                        page: 1,// what page I want to show
                        perPage: 10 // how many items I'm showing per page
                    },
                    columns: [
                    {
                        label: "Id",
                        field: 'id',
                    }, {
                        label: "Table_Number",
                        field: 'table_number',
                    }, {
                        label: 'Date',
                        field: 'date',
                        type: 'date',
                        dateInputFormat: 'YYYY-MM-DD',
                        dateOutputFormat: 'DD/MM/YYYY',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Enter a date',
                        },
                    },
                    {
                        label: 'State',
                        field: 'state',
                        filterOptions: {
                            filterValue: 'pending',
                            enabled: true,
                            filterDropdownItems: ['pending','paid','not paid'],
                        },
                    }, {
                        label: 'Meal Id',
                        field: 'meal_id',
                    }, {
                        label: 'Total Price',
                        field: 'total_price',
                    }, {
                        label: 'Responsible Waiter Id',
                        field: 'responsible_waiter_id',
                        filterOptions: {
                            enabled: true,
                            placeholder: 'Enter an Id',
                        },
                    }, {
                        label: 'Actions',
                        field: 'actions',
                        sortable: false,
                    }
                    ],
                };
            },
            methods:{
                updateParams(newProps) {
                    this.serverParams = Object.assign({}, this.serverParams, newProps);
                },

                onPageChange(params) {
                    this.updateParams({page: params.currentPage});
                    this.loadItems();
                },

                onPerPageChange(params) {
                    this.updateParams({perPage: params.currentPerPage});
                    this.loadItems();
                },

                onSortChange(params) {
                    this.updateParams({
                        sort: {
                            type: params[0].type,
                            field: params[0].field,
                        },
                    });
                    this.loadItems();
                },
                onColumnFilter(params) {
                    this.updateParams(params);
                    this.loadItems();
                },
            // load items is what brings back the rows from server
            loadItems() {
                axios.get('api/invoicesTest/?page='+this.serverParams.page,{
                    params: {
                        serverInfo:  this.serverParams
                    }
                }).then(response=> {
                    this.totalRecords = response.data[1];
                    this.rows = response.data[0].data;
                });
            },

            payInvoice(row) {
                this.$emit("pay-invoice", row);
            },
            downloadPdf(row) {

                axios.get('api/invoices/downloadPdf/' + row.id, {
                    responseType: 'blob'
                })
                .then(response=>{
                    let blobURL = window.URL.createObjectURL(response.data);
                    let tempLink = document.createElement('a');
                    tempLink.style.display = 'none';
                    tempLink.href = blobURL;
                    let filename = "invoice" + row.id + ".pdf";
                    tempLink.setAttribute('download', filename);
                    if (typeof tempLink.download === 'undefined') {
                        tempLink.setAttribute('target', '_blank');
                    }
                    document.body.appendChild(tempLink);
                    tempLink.click();
                    document.body.removeChild(tempLink);
                    window.URL.revokeObjectURL(blobURL);
                });

            },
            markInvoiceAsNotPaid(row) {
                this.$emit("invoice-not-paid", row);
                this.loadItems();
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
            },dateSFilterFn(data, filterString){
                return this.dateFilter(data,filterString);
            },
            dateEndFilterFn(data, filterString) {
                return this.dateFilter(data,filterString);
            },
            dateFilter(data,filterString){
                let date=data.split(" ");
                let days=date[0].split("-");

                data=days[2]+"/"+days[1]+"/"+days[0];

                return data.indexOf(filterString) !== -1;
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
            this.$set(this.columns[4], 'hidden', this.isManager);
        },
        components: {
            'show-message':showMessage,
        },sockets: {
            meal_terminated() {
                this.loadItems();
            },
            refresh_invoice_meals() {
                this.loadItems();
            },
            invoice_paid() {
                this.loadItems();

            },


        }
    };
</script>

<style >
.selectedRow{
    background-color: darkgrey;
}
</style>

