<div x-data="dropzone()"
@task:created.window="openTab = 2" 
x-on:livewire-upload-start="taskProcessing = true" 
@upload-start.window="taskProcessing = true"
@task:completed.window="taskProcessing = false; taskCompleted = true" 
@notify.window="let message = $event.detail[0]; if (message.type === 'error') { fileSelected = false; taskProcessing = false; taskCompleted = false; Echo.leave(`rembgtask.${window.taskUuid}`); }"
 class="py-6 px-4 sm:px-6 md:px-8 grow">
    <div class="max-w-5xl mx-auto">
        <div class="max-w-2xl mx-auto">
            <div class="flex flex-col gap-y-2 sm:gap-y-3">
                    <!-- start Tip box -->
                    <div class="z-60 absolute hidden xl:block  my-44 -mx-96 bg-gradient-to-r from-rose-700 to-rose-900 rounded-lg w-64 shadow-2xl border border-rose-950">
                        <div class="my-6 mx-6 flex flex-col gap-y-3">
                            <p class="text-left  font-inter-var text-3xl text-white tracking-wide font-extrabold">Tip</p>
                            <p class="text-left text-gray-300 text-font-bold leading-relaxed text-sm  sm:text-base">Si la photo n'est pas complètement détourée et qu'il reste quelques éléments d'arrière plan près des bordures, recommence le processus avec la photo obtenue pour éliminer les derniers éléments restants.</p>
                        </div>
                    </div>
                    <!-- end Tip box -->
                <div class="mb-2 sm:mt-4 sm:mb-6  font-inter-var text-center tracking-tight font-bold  text-3xl sm:text-5xl  text-white">Détoure grâce à l'<span class="font-inter-var text-3xl sm:text-5xl font-bold  text-rose-600"> IA</span></div>
                <p class="sm:mb-12 mb-4 text-center text-gray-300 leading-relaxed text-sm  sm:text-base">Dépose une image, l'objet au premier plan sera automatiquement détecté et détouré. Récupère une image au format PNG avec un fond transparent. Tu peux déposer des images au format PNG, JPEG, BMP ou WEBP.</p>
                <div class="{{ $task && $task->status === 'completed' ? 'visible' : 'invisible' }}">
                    <div class="w-32 h-10 flex space-x-1 p-1 bg-white rounded-lg ">
                        <button x-on:click="openTab = 1" :class="{ 'bg-rose-500 text-white': openTab === 1 }" class="text-sm w-14 flex-1 rounded-md focus:outline-none">Before</button>
                        <button x-on:click="openTab = 2" :class="{ 'bg-rose-500 text-white': openTab === 2 }" class="text-sm w-14  flex-1  rounded-md focus:outline-none">After</button>
                    </div>
                </div>
                <!-- start upload image -->
                <label for="dropzone-file">
                    <div class="relative">
                        <div 
                        @dragenter.prevent.document="onDragenter($event)"
                        @dragleave.prevent="onDragleave($event)"
                        @dragover.prevent="onDragover($event)"
                        @drop.prevent="onDrop" 
                        id="dropzone" class= "bg-noir-500 overflow-hidden w-full h-56 sm:h-96 transition duration-200 ease-in-out hover:cursor-pointer rounded-3xl shadow-3xl flex justify-center items-center">
                            <!-- The cancel task button-->
                            <button x-cloak x-show="fileSelected" x-on:click="fileSelected = false; taskProcessing = false; taskCompleted = false; Echo.leave(`rembgtask.${window.taskUuid}`);" class="z-50 m-[6px] absolute top-0 right-1 rounded-full h-7 w-7 bg-noir-900 hover:bg-gray-500  transition duration-200 ease-in-out  flex justify-center items-center text-white" type="button" wire:click="cancelRemoveBackgroundTask">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-5 h-5 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>       
                            </button>
                            <!-- end cancel task button-->
                            <!-- The processing task spinner -->
                            <div x-cloak x-show="taskProcessing" class="absolute z-50" role="status">
                                <svg aria-hidden="true" class="inline w-10 h-10 text-gray-200 animate-spin dark:text-gray-600 fill-rose-500" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentFill"/>
                                </svg>
                            </div>
                            <!-- end processing task spinner -->
                            @if ($task && $task->status == 'completed')
                                <div x-show="taskCompleted" class="w-full">
                                    <img x-show="openTab === 2" src="{{ $task->modifiedImageTemporaryUrl }}" class="object-cover w-full h-56 sm:h-96 rounded-3xl shadow-2xl">
                                    <img x-show="openTab === 1" src="{{ $task->originalImageTemporaryUrl }}" class="object-cover w-full h-56 sm:h-96 rounded-3xl shadow-2xl">
                                </div>
                            @elseif ($task && $task->status == 'uploaded')
                                <img x-show="taskProcessing || taskCompleted" src="{{ $task->originalImageTemporaryUrl }}" class="{{ $isProcessingTask ? 'blur-sm z-25 object-cover w-full h-56 sm:h-96 rounded-3xl shadow-2xl' : 'z-25 object-cover w-full h-56 sm:h-96 rounded-3xl shadow-2xl'}}">
                            @endif
                            <div x-show="!fileSelected">
                                <div x-show="!isDragging" class="flex justify-center items-center flex-col gap-y-2">
                                    <div  class="group bg-rose-600 hover:bg-rose-700 px-4 py-3  text-center text-sm font-semibold inline-block text-white cursor-pointer uppercase transition duration-200 ease-in-out rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-800 focus-visible:ring-offset-2 active:scale-95">
                                        <div class="flex items-center justify-center gap-x-3">
                                            <div class="w-7 h-7 border-2 border-white group-hover:bg-rose-400 transition duration-200 ease-in-out rounded-full flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-8 w-8 stroke-current text-white">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-6-6h12" />
                                                </svg>
                                            </div>
                                            <p class="text-white">Add image</p>
                                        </div>
                                    </div>
                                    <div class="font-bold font-inter-var text-white text-base sm:text-xl">Or drag an image here</div>
                                </div>
                                <!-- end Add button-->
                                <!-- upload image-->
                                <div x-cloak x-show="isDragging" class="pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="text-rose-500 pointer-events-none">
                                        <path d="M10 2a.75.75 0 01.75.75v5.59l1.95-2.1a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0L6.2 7.26a.75.75 0 111.1-1.02l1.95 2.1V2.75A.75.75 0 0110 2z" />
                                        <path d="M5.273 4.5a1.25 1.25 0 00-1.205.918l-1.523 5.52c-.006.02-.01.041-.015.062H6a1 1 0 01.894.553l.448.894a1 1 0 00.894.553h3.438a1 1 0 00.86-.49l.606-1.02A1 1 0 0114 11h3.47a1.318 1.318 0 00-.015-.062l-1.523-5.52a1.25 1.25 0 00-1.205-.918h-.977a.75.75 0 010-1.5h.977a2.75 2.75 0 012.651 2.019l1.523 5.52c.066.239.099.485.099.732V15a2 2 0 01-2 2H3a2 2 0 01-2-2v-3.73c0-.246.033-.492.099-.73l1.523-5.521A2.75 2.75 0 015.273 3h.977a.75.75 0 010 1.5h-.977z" />
                                    </svg>
                                    <p class="text-base text-white font-inter-var font-bold">Drop here</p>
                                </div>
                                <!-- end upload svg-->
                            </div>
                        </div>
                    </div>
                     <!-- Download button-->
                    @if ($task && $task->status == 'completed')
                        <div x-show="openTab === 2 && taskCompleted" class="mt-4 flex justify-center">
                            <button wire:click="download" class="bg-rose-500 px-4 py-3 w-40 text-center text-sm font-semibold inline-block text-white cursor-pointer uppercase transition duration-200 ease-in-out rounded-md hover:bg-rose-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-700 focus-visible:ring-offset-2 active:scale-95">
                                <div class="flex flex-row  gap-x-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>                      
                                    <p>Download</p>
                                </div>
                            </button>
                        </div>
                    @endif
                    <!-- end download button-->
                    <input x-bind:disabled="fileSelected" x-on:change="fileSelected = true" class="hidden" id="dropzone-file" type="file" wire:model="image"></input>   
                </label>
                <!-- end upload image -->     
            </div>
        </div> 
        <x-notification />
    </div>
    @script
    <script>
        Alpine.data('dropzone', () => {
            return ({
                openTab: 2,
                isDragging: false,
                isDropped: false,
                fileSelected: false,
                taskProcessing: false,
                taskCompleted: false,

                onDrop(e) {
                    if (!this.fileSelected) {
                        this.isDropped = true
                        this.isDragging = false

                        let file; // Declare file variable outside of event handling scope
                        if (event.dataTransfer.items) {
                            // Use DataTransferItemList interface to access the file(s)
                            for (const item of event.dataTransfer.items) {
                                // If dropped item isn't a file, skip it
                                if (item.kind === "file") {
                                    file = item.getAsFile();
                                    // Process the first file and break out of the loop
                                    break;
                                } else {
                                    $wire.dispatch('removeBackgroundTaskFailed',  { exceptionMessage: "Make sure it has an appropriate image format (jpeg,png,jpg,webp,bmp) and that it does not exceed 50mo.", exceptionTitle: "Image upload Failure" });
                                    return;
                                }
                            }
                        } else {
                            // Use DataTransfer interface to access the file(s)
                            file = event.dataTransfer.files[0];

                        }

                        // Check if file is undefined and return or perform necessary action
                        if (!file) {
                            console.log('bug')
                            // File is undefined, handle the condition here (e.g., return)
                            return;
                        }
                        if (!file.type) {
                            $wire.dispatch('removeBackgroundTaskFailed',  { exceptionMessage: "Make sure it has an appropriate image format (jpeg,png,jpg,webp,bmp) and that it does not exceed 50mo.", exceptionTitle: "Image upload Failure" });
                            return;
                        }
                        
                        window.dispatchEvent(new Event('upload-start'));
                        this.fileSelected = true
                        $wire.upload('image', file, () => {
                            // Success callback...
                        }, () => {
                            //
                        }, (event) => {
                            // Progress callback...
                            // event.detail.progress contains a number between 1 and 100 as the upload progresses
                        }, () => {
                            // Cancelled callback...
                        })

                    }
                    
                },
                onDragenter() {
                    if (event.relatedTarget && event.relatedTarget.id !== 'dropzone') {
                        return
                    }   
                    this.isDragging = true  
                },
                onDragleave() {
                    if (dropzone.contains(event.relatedTarget) ) {
                        return
                    }
                    this.isDragging = false
                    
                },               
                onDragover() {
                    this.isDragging = true
                },
            });
        });
        
        $wire.on('task:created', (task) => {
            const taskUuid = task[0];
            window.taskUuid = taskUuid;
            Echo.channel(`rembgtask.${taskUuid}`).listen('RemoveBackgroundTaskCompleted', (e) => {
                $wire.dispatch('removeBackgroundTaskCompleted');
                const taskCompletedEvent = new Event('task:completed');
                window.dispatchEvent(taskCompletedEvent);
            })
            .listen('RemoveBackgroundTaskFailed', (exception) => {
                exceptionMessage = exception.exceptionMessage;
                exceptionTitle = exception.exceptionTitle;
                $wire.dispatch('removeBackgroundTaskFailed',  { exceptionMessage: exceptionMessage, exceptionTitle: exceptionTitle });
            })
        });
    </script>
    @endscript
</div>