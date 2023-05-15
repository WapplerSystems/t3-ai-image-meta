
import {html, LitElement, nothing} from "lit";
import {customElement, property} from "lit/decorators.js";
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";
import DocumentService from "@typo3/core/document-service.js";
import DebounceEvent from "@typo3/core/event/debounce-event.js";
import RegularEvent from "@typo3/core/event/regular-event.js";
import Icons, {IconStyles} from "@typo3/backend/icons.js";
import {styleTag} from "@typo3/core/lit-helper.js";
import {unsafeHTML} from "lit/directives/unsafe-html.js";
import {until} from "lit/directives/until.js";


let DescriptionButtonElement = class extends LitElement {
  constructor() {
    super(...arguments),
    this.size = Sizes.default,
    this.state = States.default,
    this.overlay = null,
    this.markup = MarkupIdentifiers.inline,
    this.raw = null
  }

  render() {
    if (this.raw) return html`${unsafeHTML(this.raw)}`;
    if (!this.identifier) return nothing;
    const e = Icons.getIcon(this.identifier, this.size, this.overlay, this.state, this.markup).then((e => html`
        ${styleTag(IconStyles.getStyles())}
        <div class="icon-wrapper">
            ${unsafeHTML(e)}
        </div>
    `)), t = document.createElement("typo3-backend-spinner");
    return t.size = this.size, html`${until(e, html`${t}`)}`
  }
};


class DescriptionSuggestButton {
  constructor(e,t) {
    this.fullElement = null,
    this.manuallyChanged = !1,
    this.inputField = null,
    this.request = null,
    this.options = t;
    this.currentRequest = null;
    DocumentService.ready().then((t => {

      document.querySelectorAll(".t3js-formengine-textarea").forEach((e => {
        let a = JSON.parse(e.attributes.getNamedItem('data-formengine-field-change-items').value);
        if (a[0]['data']['fieldName'] == 'description') {
          let u = document.createElement('button');
          u.classList.add('btn');
          u.classList.add('btn-primary');
          u.innerHTML = 'Suggest';

          u.addEventListener("click", (t => {
            t.preventDefault();
            this.doRequest(e,a[0]['data']['identifier']);
          }));

          e.parentElement.append(u);

        }
      }));
      return;

      Icons.getIcon('spinner-circle-light', Icons.sizes.small, null, 'disabled');

      const e = Icons.getIcon('spinner-circle-light', Icons.sizes.small, null, 'disabled').then((e => html`
        ${styleTag(IconStyles.getStyles())}
        <div class="icon-wrapper">
            ${unsafeHTML(e)}
        </div>
      `));

      //t = document.createElement("typo3-backend-spinner");

      //this.registerEvents();
    }))
  }

  doRequest(e, fileMetaId) {

    this.currentRequest = new AjaxRequest(TYPO3.settings.ajaxUrls.image_description_suggest), this.currentRequest.post({
      fileUid: fileMetaId
    }).then((async z => {
      const o = await z.resolve("application/json");
      e.value = o.captionResult.text;
    }));

  }

  registerEvents() {

    const e = Object.values(this.getAvailableFieldsForProposalGeneration()).map((e => `[id="${e.id}"]`)),
      t = this.fullElement.querySelector(Selectors.recreateButton);
    e.length > 0 && "new" === this.options.command && new DebounceEvent("input", (() => {
      this.manuallyChanged || this.sendSlugProposal(ProposalModes.AUTO)
    })).delegateTo(document, e.join(",")), e.length > 0 || this.hasPostModifiersDefined() ? new RegularEvent("click", (e => {
      e.preventDefault(), this.readOnlyField.classList.contains("hidden") && (this.readOnlyField.classList.toggle("hidden", !1), this.inputField.classList.toggle("hidden", !0)), this.sendSlugProposal(ProposalModes.RECREATE)
    })).bindTo(t) : (t.classList.add("disabled"), t.disabled = !0), new DebounceEvent("input", (() => {
      this.manuallyChanged = !0, this.sendSlugProposal(ProposalModes.MANUAL)
    })).bindTo(this.inputField);
    const s = this.fullElement.querySelector(Selectors.toggleButton);
    new RegularEvent("click", (e => {
      e.preventDefault();
      const t = this.readOnlyField.classList.contains("hidden");
      this.readOnlyField.classList.toggle("hidden", !t), this.inputField.classList.toggle("hidden", t), t ? (this.inputField.value !== this.readOnlyField.value ? this.readOnlyField.value = this.inputField.value : (this.manuallyChanged = !1, this.fullElement.querySelector(".t3js-form-proposal-accepted").classList.add("hidden"), this.fullElement.querySelector(".t3js-form-proposal-different").classList.add("hidden")), this.hiddenField.value = this.readOnlyField.value) : this.hiddenField.value = this.inputField.value
    })).bindTo(s)
  }

  sendSlugProposal(e) {
    const t = {};
    e === ProposalModes.AUTO || e === ProposalModes.RECREATE ? (Object.entries(this.getAvailableFieldsForProposalGeneration()).forEach((e => {
      t[e[0]] = e[1].value
    })), !0 === this.options.includeUidInValues && (t.uid = this.options.recordId.toString())) : t.manual = this.inputField.value, this.request instanceof AjaxRequest && this.request.abort(), this.request = new AjaxRequest(TYPO3.settings.ajaxUrls.record_slug_suggest), this.request.post({
      values: t,
      mode: e,
      tableName: this.options.tableName,
      pageId: this.options.pageId,
      parentPageId: this.options.parentPageId,
      recordId: this.options.recordId,
      language: this.options.language,
      fieldName: this.options.fieldName,
      command: this.options.command,
      signature: this.options.signature
    }).then((async t => {
      const s = await t.resolve(), l = "/" + s.proposal.replace(/^\//, ""),
        i = this.fullElement.querySelector(".t3js-form-proposal-accepted"),
        o = this.fullElement.querySelector(".t3js-form-proposal-different");
      i.classList.toggle("hidden", s.hasConflicts), o.classList.toggle("hidden", !s.hasConflicts), (s.hasConflicts ? o : i).querySelector("span").innerText = l;
      this.hiddenField.value !== s.proposal && this.fullElement.querySelector("input[data-formengine-input-name]").dispatchEvent(new Event("change", {
        bubbles: !0,
        cancelable: !0
      })), e === ProposalModes.AUTO || e === ProposalModes.RECREATE ? (this.readOnlyField.value = s.proposal, this.hiddenField.value = s.proposal, this.inputField.value = s.proposal) : this.hiddenField.value = s.proposal
    })).finally((() => {
      this.request = null
    }))
  }

  getAvailableFieldsForProposalGeneration() {
    const e = {};
    for (const [t, s] of Object.entries(this.fieldsToListenOn)) {
      let l = document.querySelector('[data-formengine-input-name="' + s + '"]');
      null === l && (l = document.querySelector('select[name="' + s + '"]')), null !== l && (e[t] = l)
    }
    return e
  }

  hasPostModifiersDefined() {
    return Array.isArray(this.options.config.generatorOptions.postModifiers) && this.options.config.generatorOptions.postModifiers.length > 0
  }
}

export default new DescriptionSuggestButton;
