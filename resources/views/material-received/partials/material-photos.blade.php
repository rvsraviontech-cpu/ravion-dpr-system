{{-- Material receipt photos --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">

    <div class="flex flex-col gap-3 border-b border-gray-200 bg-[#0F2A52] p-4 text-white md:flex-row md:items-center md:justify-between md:p-5">

        <div>
            <h2 class="text-lg font-bold text-white sm:text-xl">
                Material Receipt Photos
            </h2>

            <p class="mt-1 text-xs text-blue-100 sm:text-sm">
                Upload delivery, challan, invoice, unloading, condition or material-specific photos.
            </p>
        </div>

        <button type="button"
                id="add-photo-row"
                class="w-full rounded-lg bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700 md:w-auto md:py-2">
            + Add Photo
        </button>

    </div>

    <div class="border-b border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-800 lg:hidden">
        Use the phone camera or gallery for delivery, challan, unloading and material-condition evidence. Add more rows for multiple photos.
    </div>

    <div id="photo-rows"
         class="divide-y divide-gray-200">

        @foreach($oldPhotos as $photoIndex => $oldPhoto)

            <div class="photo-row p-3 sm:p-5"
                 data-photo-index="{{ $photoIndex }}">

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-start">

                    <div class="lg:col-span-2">
                        <label class="{{ $labelClass }}">
                            Photo Type
                        </label>

                        <select name="photos[{{ $photoIndex }}][photo_type]"
                                class="{{ $inputClass }} photo-type-select">

                            @foreach($photoTypes as $photoType)
                                <option value="{{ $photoType }}"
                                    {{ ($oldPhoto['photo_type'] ?? 'Material Photo') === $photoType ? 'selected' : '' }}>
                                    {{ $photoType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="{{ $labelClass }}">
                            Material Item
                        </label>

                        <select name="photos[{{ $photoIndex }}][item_index]"
                                class="{{ $inputClass }} photo-item-select"
                                data-selected="{{ $oldPhoto['item_index'] ?? '' }}">

                            <option value="">
                                General / Whole Receipt
                            </option>
                        </select>

                        <p class="mt-1 text-xs text-gray-500">
                            Choose a material only when the photo relates specifically to that item.
                        </p>
                    </div>

                    <div class="lg:col-span-3">
                        <label class="{{ $labelClass }}">
                            Caption
                        </label>

                        <input type="text"
                               name="photos[{{ $photoIndex }}][caption]"
                               value="{{ $oldPhoto['caption'] ?? '' }}"
                               class="{{ $inputClass }}"
                               maxlength="500"
                               placeholder="Optional photo description">
                    </div>

                    <div class="lg:col-span-3">
                        <label class="{{ $labelClass }}">
                            Image
                        </label>

                        <input type="file"
                               name="photos[{{ $photoIndex }}][file]"
                               class="{{ $inputClass }} photo-file-input"
                               accept="image/jpeg,image/png,image/webp,image/*">

                        <p class="mt-1 text-xs text-gray-500">
                            JPG, PNG or WEBP. Maximum 10 MB.
                        </p>
                    </div>

                    <div class="lg:col-span-1 lg:pt-6">
                        <button type="button"
                                class="remove-photo-row w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            Remove
                        </button>
                    </div>

                </div>

                <div class="photo-preview-wrapper mt-4 hidden">
                    <div class="flex flex-wrap items-start gap-4 rounded-lg border border-gray-200 bg-gray-50 p-3">

                        <img src=""
                             alt="Photo preview"
                             class="photo-preview h-28 w-36 rounded-lg border border-gray-200 bg-white object-cover">

                        <div class="text-sm text-gray-600">
                            <p class="font-semibold text-gray-800">
                                Preview
                            </p>

                            <p class="photo-file-name mt-1"></p>
                            <p class="photo-file-size mt-1 text-xs text-gray-500"></p>
                        </div>

                    </div>
                </div>

            </div>

        @endforeach

    </div>

    <div class="border-t border-gray-200 p-4 text-xs text-gray-500 sm:p-5 sm:text-sm">
        Stored filenames follow:
        <span class="font-semibold text-gray-700">
            Project-Material-PhotoType-Engineer-YYYYMMDD-HHMMSS-Sequence.ext
        </span>
    </div>

</div>
