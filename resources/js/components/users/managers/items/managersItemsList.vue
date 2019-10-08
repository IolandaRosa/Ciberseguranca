<template>
	<div>
		<p class="h5"><strong>Items</strong></p>

		<vue-good-table :columns="columns" :rows="items" :pagination-options="{ enabled: true, perPage: 4}" :search-options="{ enabled: true}">
			<div slot="table-actions">
				<button class="btn btn-outline-info btn-xs" @click="createItem"><i class='fas fa-plus'>&nbsp;</i>New Item</button>
			</div>
			<template slot="table-row" slot-scope="props">
				<span v-if="props.column.field == 'photo_url'" >
					<img :src="'storage/items/'+props.row.photo_url" alt="Item Photo" width="50" height="60" class="rounded-circle">
				</span>
				<span v-if="props.column.field == 'actions'">
					<button class="btn btn-outline-success btn-xs" @click="editItem(props.row.id)"><i class="far fa-edit"></i>Edit</button>
					<button class="btn btn-outline-danger btn-xs" @click="deleteItem(props.row.id)"><i class="fas fa-trash-alt"></i>Delete</button>
				</span>
				<span v-if="props.column.field != 'actions' && props.column.field != 'photo_url'">
					{{props.formattedRow[props.column.field]}}
				</span>

			</template>
		</vue-good-table>
	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	export default {
		props:['items'],
		data:
		function() {
			return {
				columns: [
				{
					label:'Id',
					field:'id',
					sortable:true,
					type: 'number',
				},
				{
					label: '',
					field: 'photo_url',
					html: true,
					sortable:false,
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
					type: 'number',
				}, {
					label: 'Actions', 
					field: 'actions',
					sortable:false,
				}
				],

			};
		},
		methods:{
			createItem(){
				this.$emit('item-create');
			},
			editItem(item_id){
				this.$emit('item-edit',item_id);
			},
			deleteItem(item_id){
				this.$emit('item-delete',item_id);
			}
		},

	};
</script>