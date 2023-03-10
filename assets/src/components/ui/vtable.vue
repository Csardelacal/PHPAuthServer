<template>
	<div class="p-4">
		<div class="py-2 bg-gray-50 rounded border border-gray-200">
			<table class="w-full border-collapse">
				<thead>
					<tr>
						<td v-for="(i, idx) in headers" :key="idx">
							{{ i }}
						</td>
					</tr>
				</thead>
				<slot></slot>
			</table>
		</div>
	</div>
</template>

<style lang="scss" scoped>
thead td {
	@apply px-4 py-2 font-bold;
}
</style>

<script setup lang="ts">
withDefaults(
	defineProps<{
		headers: Array<string>
	}>(),
	{
		headers: () => []
	}
);
</script>
