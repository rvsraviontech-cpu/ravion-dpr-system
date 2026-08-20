<x-guest-layout>

    <div class="mb-6">

        <h1 class="text-xl font-bold text-gray-900">
            Change Your Password
        </h1>

        <p class="mt-2 text-sm leading-6 text-gray-600">
            Your password was created or reset by an administrator.
            For security, you must create a new password before continuing to Ravion ERP.
        </p>

    </div>

    @if ($errors->any())

        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3">

            <div class="text-sm font-semibold text-red-800">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif

    <form method="POST"
          action="{{ route('password.change-required.update') }}"
          class="space-y-5">

        @csrf
        @method('PUT')

        <div>

            <label for="password"
                   class="mb-1.5 block text-sm font-semibold text-gray-700">
                New Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            >

        </div>

        <div>

            <label for="password_confirmation"
                   class="mb-1.5 block text-sm font-semibold text-gray-700">
                Confirm New Password
            </label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
            >

        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">

            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                Password Requirements
            </div>

            <div class="mt-2 text-sm leading-6 text-gray-600">
                Use at least 8 characters with uppercase and lowercase letters
                and at least one number. Do not reuse your temporary password.
            </div>

        </div>

        <button
            type="submit"
            class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
            Change Password & Continue
        </button>

    </form>

</x-guest-layout>