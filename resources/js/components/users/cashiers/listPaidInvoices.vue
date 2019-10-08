<template>
    <div>
        <div v-if="this.$store.state.user != null">

            <vue-good-table ref="table" mode="remote"  :columns="columns" :rows="rows" :pagination-options="{ enabled: true}"
            @on-row-click="onRowClick" :row-style-class="rowStyleFn"
            @on-page-change="onPageChange"
            @on-sort-change="onSortChange"
            @on-column-filter="onColumnFilter"
            @on-per-page-change="onPerPageChange"
            :totalRows="totalRecords">

            <template slot="table-row" slot-scope="props">

                <span v-if="props.column.field=='actions'">
                    <span>
                        <button @click="downloadPdf(props.row)" class="btn btn-outline-info btn-xs">Download PDF</button>
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

    export default {
        data:
        function() {
            return {
                showMessage:false,
                message:'',
                typeofmsg: "",
                selectedRow: null,
                rows: [],
                totalRecords: 0,
                serverParams: {
                    columnFilters: {

                    },
                    sort: {
                        field: '',
                        type: '',
                    },
                    page: 1,
                    perPage: 10,
                },
                columns: [
                {
                    label: "Id",
                    field: 'id',
                }, {
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
                    label: 'Actions',
                    field: 'actions',
                    sortable: false,
                }
                ],
            };
        },
        methods:{
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
            onRowClick(params){
                if(this.showSelected == true)
                {
                    this.selectedRow = params.row.originalIndex;
                }
            },rowStyleFn(row) {
                return this.selectedRow === row.originalIndex  && this.showSelected == true?'selectedRow':'';
            },
            close(){
            },

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
            loadItems() {

                axios.get('api/invoices/paid?page='+this.serverParams.page,{
                    params: {
                        serverInfo:  this.serverParams
                    }
                })
                .then(response=>{
                    this.totalRecords = response.data[1];
                    this.rows = response.data[0].data;
                });
            },
        },
        sockets: {
            new_pending_invoice() {
                this.loadItems();
            },
        }

    };
</script>

