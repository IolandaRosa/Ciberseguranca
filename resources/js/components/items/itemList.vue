<template> 
    <div>
        <vue-good-table :columns="columns" :rows="items" :pagination-options="{ enabled: true, perPage: 5}" :search-options="{ enabled: true}" @on-row-click="onRowClick" :row-style-class="rowStyleFn" >
            <template slot="table-row" slot-scope="props">
                <span v-if="props.column.field == 'photo_url'" >
                 <img :src="'storage/items/'+props.row.photo_url" alt="Item Photo" width="50" height="60">
             </span>
             <span v-else>
                {{props.formattedRow[props.column.field]}}
            </span>    
        </template>
    </vue-good-table>
</div>  
</template> 


<script type="text/javascript">
    /*jshint esversion: 6 */

    export default {
        props: ['items','showSelected'],
        data:
        function() {
            return {
                selectedRow: null,
                columns: [
                {
                    label: '',
                    field: 'photo_url',
                    html: true,
                }, {
                    label: 'Name', 
                    field: 'name',
                }, {
                    label: 'Type', 
                    field: 'type',
                }, {
                    label: 'Description', 
                    field: 'description',
                }, {
                    label: 'Price', 
                    field: 'price',
                }
                ],
            };
        },
        methods:{
            onRowClick(params){
                if(this.showSelected == true)
                {
                    this.selectedRow = params.row.originalIndex;
                    this.$emit('selectedRow',this.selectedRow);
                }
            },rowStyleFn(row) {

                return this.selectedRow === row.originalIndex  && this.showSelected == true?'selectedRow':'';
            },

        },

    };
</script>

<style >
.conf{
    font-weight: bold;
    background: #123456  !important;
    color: #fff          !important;
    padding: 0px 5px;
}
.selectedRow{
    background-color: darkgrey;
}

</style>
