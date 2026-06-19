<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<script>
export default {
  layout: null,
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative bg-gradient-to-br from-amber-600 via-amber-700 to-amber-800 overflow-hidden">
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-amber-500/20 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>

        <AuthenticationCard class="relative">
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-2">Reset Password</h2>
            <p class="text-sm text-gray-500 text-center mb-6">Enter your email to receive a reset link</p>

            <div v-if="status" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Send Reset Link
                    </PrimaryButton>
                </div>
            </form>

            <div class="mt-6 text-center">
                <Link :href="route('login')" class="text-sm text-amber-600 hover:text-amber-700 font-medium">
                    ← Back to login
                </Link>
            </div>
        </AuthenticationCard>
    </div>
</template>