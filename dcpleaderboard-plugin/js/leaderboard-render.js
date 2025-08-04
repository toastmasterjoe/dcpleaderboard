 
function render_category(data, type, row){
                            console.log(row.ti_status);
                            var category = '';
                            switch(data){
                                case '':
                                    category = 'D';
                                    break;
                                case 'D':
                                    category = 'C';
                                    break;
                                case 'S':
                                    category = 'B';
                                    break;
                                case 'P':
                                    category = 'A';
                                    break;
                                default:
                                    category = 'D';
                                    break;
                            }
                            var newCategory = '';
                            switch(row.ti_status){
                                case '':
                                    newCategory = 'D';
                                    break;
                                case 'D':
                                    newCategory = 'C';
                                    break;
                                case 'S':
                                    newCategory = 'B';
                                    break;
                                case 'P':
                                    newCategory = 'A';
                                    break;
                                default:
                                    newCategory = 'A';
                                    break;
                            }
                            return `
                            <div class="progress-cell">
                                <div class="progress-text" >${( (newCategory < category) ?'<span class="promotion-marker">&#9650;</span>': '<span class="promotion-marker">&nbsp;</span>')}
                                    Serie ${( (newCategory < category) ? newCategory : category )}
                                </div>
                            </div>
                            `;
                        }