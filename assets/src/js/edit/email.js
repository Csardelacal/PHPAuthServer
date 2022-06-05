import { createApp } from 'vue';
import eMailForm from '/assets/src/components/edit/email.vue'

const app = createApp({
  /* root component options */
  components: {
	  'emailform' : eMailForm
  }
});

app.mount("#email-address-change");
