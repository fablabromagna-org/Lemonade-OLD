<article class="message is-info captcha-container">
    <div class="message-header">
        <p>Non sono un robot</p>
    </div>
    <div class="message-body">
        <img class="captcha-img" src="/ajax/captcha.php" style="border-radius: 3px;" alt/>
        <div class="columns is-mobile">
            <div class="column">
                <div class="field">
                    <div class="control">
                        <input class="input is-info" type="text" name="captcha"
                               placeholder="Codice di verifica"/>
                    </div>
                </div>
            </div>
            <div class="column is-narrow">
                <a class="button"
                   onclick="$($(this).parents('.captcha-container')[0]).find('.captcha-img')[0].src='/ajax/captcha.php';$($(this).parents('.captcha-container')[0]).find('input')[0].value='';">
                                            <span class="icon is-small">
                                                <i class="fas fa-sync-alt"></i>
                                            </span>
                </a>
            </div>
        </div>
    </div>
</article>
