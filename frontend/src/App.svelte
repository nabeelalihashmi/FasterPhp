<script>
	import ajaxify from "./ajaxify.js";

	let s = true;
	async function before(event) {
		console.log("before", event);
		s = false;
	}

	async function after(event) {
		const result = await event.detail.res;
		const ret = await result.json();
		alert(ret.message);
		s = true;
	}
</script>

<main>
	<h1>FasterPhp + Svelte</h1>
	<div>
		<h1>Ajax Form</h1>
		<form
			action=""
			method="post"
			enctype="multipart/form-data"
			use:ajaxify
			on:form-submitting={before}
			on:form-submitted={after}
		>
			<input type="text" name="first_name" />
			<input type="text" name="last_name" />
			{#if s}
				<input type="submit" value="Ajax Submit" />
			{/if}
		</form>
	</div>

	<div>
		<h1>Non Ajax Form</h1>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="text" name="first_name" />
			<input type="text" name="last_name" />
			<input type="submit" />
		</form>
	</div>
</main>

<style>
	main {
		align-items: center;
		display: grid;
		grid-template-columns: 1fr 1fr;
		column-gap: 20px;
		height: 100vh;
	}
	div {
		margin-bottom: 20px;
		border: 1px solid black;
		box-shadow: 4px 4px 4px #ccc;
		padding: 10px;
	}

	input {
		display: block;
		margin-bottom: 8px;
		width: 90%;
		margin-left: auto;
		margin-right: auto;
		padding: 0px;
		box-sizing: border-box;
	}

	h1 {
		font-size: 1.4em;
		text-align: center;
		text-shadow: 4px 5px 0px #cfc;
	}
</style>
