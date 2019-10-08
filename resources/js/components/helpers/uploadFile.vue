<template>

	<div>
		<label for="chooseFile">Select a picture of the worker</label>
		<div class="custom-file" id="chooseFile">
			<input type="file" class="custom-file-input" id="inputGroupFile01" aria-describedby="inputGroupFileAddon01" v-on:change="onFileChanged" accept=".jpg,.jpeg,.png">
			<label class="custom-file-label" for="inputGroupFile01"> {{ filename }} </label>
		</div>
	</div>

</template>

<script type="text/javascript">
    /*jshint esversion: 6 */

    export default {
		data() {
			return { 
				filename: 'Choose a file',
				file: '',
			};
		}, 
		methods: {
			onFileChanged() {
				this.file = event.target.files[0];
				this.filename = this.file.name;
				this.$emit('fileChanged', this.file);
			}
		}
	};	
</script>