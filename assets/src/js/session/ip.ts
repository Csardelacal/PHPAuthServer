
import { createApp } from "vue";

/**
 * Bring in any components we may use in the top level app.
 */
import session from "../../components/ui/session.vue";
import vtable from "../../components/ui/vtable.vue";

const app = createApp({
	/**
	 * Components we need for creating our application
	 */
	components: {
		vtable,
		session
	},
	/**
	 * Initial application setup.
	 * 
	 * @returns object
	 */
	setup: function () : object {
		return {
			
		};
	}
});

app.mount('#app')
