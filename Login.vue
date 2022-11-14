<template>
	<div
		class="c-app flex-row align-items-center"
		style="position: relative"
	>
		<div class="bottom-left">
			<div class="object">
				<i
					class="fal fa-hexagon"
					style="color: #f48830; font-size: 140px"
				></i>
			</div>
			<div class="object2">
				<i
					class="fal fa-hexagon"
					style="color: #fda82f; font-size: 100px"
				></i>
			</div>
			<div class="object3">
				<i
					class="fal fa-hexagon"
					style="color: #508177; font-size: 250px"
				></i>
			</div>
			<div class="object4">
				<i
					class="fal fa-hexagon"
					style="color: #94c3e3; font-size: 50px"
				></i>
			</div>
		</div>
		<div class="top-right">
			<div class="object5">
				<i
					class="fal fa-hexagon"
					style="color: #f48830; font-size: 140px"
				></i>
			</div>
			<div class="object6">
				<i
					class="fal fa-hexagon"
					style="color: #fda82f; font-size: 100px"
				></i>
			</div>
			<div class="object7">
				<i
					class="fal fa-hexagon"
					style="color: #508177; font-size: 250px"
				></i>
			</div>
			<div class="object8">
				<i
					class="fal fa-hexagon"
					style="color: #94c3e3; font-size: 50px"
				></i>
			</div>
		</div>
		<CContainer style="margin-top: 50px">
			<CRow class="justify-content-center">
				<CCol md="6">
					<CCardGroup>
						<CCard
							class="p-4"
							style="
								border: 1px solid #c4c4c4;
								background-filter: blur(2px);
							"
						>
							<CCardBody>
									<div class="logo mb-3">
										<img
											class="logo"
											:src="image"
											alt="supersync"
										/>
									</div>

								<CForm
									@submit.prevent="login"
									method="POST"
								>
									<h2 class="text-center">Log in to your account</h2>
									<Flash :key="flashKey"></Flash>
									<CInput
										v-model="form.email"
										placeholder="User Email Address"
										autocomplete="user email"
									>
										<template #prepend-content></template>
									</CInput>
									<CInput
										v-model="form.password"
										placeholder="Password"
										type="password"
										autocomplete="curent-password"
									>
										<template #prepend-content></template>
									</CInput>
									<CRow>
										<CCol>
											<CButton
												type="submit"
												name="Login"
												class="call-to-action-btn mt-2"
											>Log in</CButton>
										</CCol>
									</CRow>
									<CRow>
										<CCol class="text-center font-pem875">
											<inertia-link :href="route('register')">
												Not registered? Register here!
											</inertia-link>
										</CCol>
									</CRow>
									<br />
									<CRow>
										<CCol class="text-center font-pem875" >
											<inertia-link :href="route('password.forgot')">
												Forgot Password?
											</inertia-link>
										</CCol>
									</CRow>
									<div class="copyright mt-4">
										© {{ currentYear }} SuperSync
									</div>
								</CForm>
							</CCardBody>
						</CCard>
					</CCardGroup>
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>
<script>
import Flash from "@/Shared/Flash";
import Helpers from "../../Helpers/Helpers";

export default {
	name: "Login",
	components: {
		Flash,
	},
	data() {
		return {
			currentYear: this.getYear(),
			form: {
				email: new String(),
				password: new String(),
				timezone: Helpers.defaultTimezone(),
			},
			showMessage: false,
			message: "",
			image: "/images/supersync-dark.png",
			flashKey: 0,
		};
	},
	mounted() {
		this.triggerFlash();
	},
	methods: {
		goRegister() {
			// TODO
		},
		triggerFlash() {
			this.flashKey = !this.flashKey;
			return;
		},
		login() {
			this.$inertia.post(route("logging-in"), this.form).then(() => {
				this.triggerFlash();
			});
		},
		getYear() {
			let date = new Date();
			let currentYear = date.getFullYear();
			return currentYear;
		},
	},
};
</script>
