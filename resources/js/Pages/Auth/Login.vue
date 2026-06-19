<script>
export default {
  layout: null,
};
</script>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="min-h-screen flex items-center justify-center px-4 py-12 relative bg-gradient-to-br from-amber-600 via-amber-700 to-amber-800 overflow-hidden">
        <!-- Background pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 40px 40px;"></div>
        <div class="absolute top-0 right-0 w-[500px] h-[500px] lg:w-[600px] lg:h-[600px] bg-white/10 rounded-full blur-3xl translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] lg:w-[500px] lg:h-[500px] bg-amber-500/20 rounded-full blur-3xl -translate-x-1/2 translate-y-1/2"></div>

        <!-- Login Card -->
        <div class="relative w-full max-w-md">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-8 lg:p-10">
                <div class="flex justify-center mb-6">
                    <Link :href="route('home')">
                        <img src="/images/hbci-logo-no-text.png" alt="Honey Bee Culinary Institute" class="h-16">
                    </Link>
                </div>

                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-2">Welcome Back</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-6">Sign in to your respective dashboard!</p>

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
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox v-model:checked="form.remember" name="remember" />
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                        <Link :href="route('password.request')" class="text-amber-600 hover:text-amber-700 font-semibold">
                            Forgot password?
                        </Link>
                    </div>

                    <div>
                        <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Sign In
                        </PrimaryButton>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Don't have an account?
                        <Link :href="route('apply')" class="text-amber-600 hover:text-amber-700 font-semibold">
                            Apply now
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>