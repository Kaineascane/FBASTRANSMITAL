'use client';

import { useEffect, useState } from 'react';
import { SI_PER_PAD } from '@/lib/constants';

type Props = {
  errors: string[];
  nextPad: number;
  nextSi: number;
  lastSiEnd: number;
  today: string;
  defaults: {
    from_branch: string;
    to_branch: string;
    released_by: string;
    date_released: string;
    starting_pad: number;
    starting_si: number;
    total_pads: string | number;
  };
};

export default function TransmittalForm({ errors, nextPad, nextSi, lastSiEnd, today, defaults }: Props) {
  const [preview, setPreview] = useState('Enter total pads to preview S.I ranges…');

  useEffect(() => {
    if (errors.length > 0) {
      document.querySelector('.alert-error')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }, [errors]);

  function updatePreview(form: HTMLFormElement) {
    const pad = parseInt((form.elements.namedItem('starting_pad') as HTMLInputElement).value, 10) || 0;
    const si = parseInt((form.elements.namedItem('starting_si') as HTMLInputElement).value, 10) || 0;
    const total = parseInt((form.elements.namedItem('total_pads') as HTMLInputElement).value, 10) || 0;
    if (!total || !pad || !si) {
      setPreview('Enter total pads to preview S.I ranges…');
      return;
    }
    const lines: string[] = [];
    let curPad = pad;
    let curSi = si;
    const show = Math.min(total, 3);
    for (let i = 0; i < show; i++) {
      const end = curSi + SI_PER_PAD - 1;
      lines.push(`Pad ${curPad}: ${curSi}–${end}`);
      curPad++;
      curSi = end + 1;
    }
    if (total > show) lines.push(`… +${total - show} more pad(s)`);
    setPreview('Preview: ' + lines.join(' · '));
  }

  return (
    <>
      {errors.length > 0 && (
        <div className="alert alert-error" role="alert">
          <strong>
            <i className="bi bi-exclamation-triangle-fill" /> Please fix the following:
          </strong>
          <ul className="mb-0 mt-2">
            {errors.map((err) => (
              <li key={err}>{err}</li>
            ))}
          </ul>
        </div>
      )}

      <div className="card-panel">
        <form
          action="/api/transmittal"
          method="POST"
          id="transmittalForm"
          onChange={(e) => updatePreview(e.currentTarget)}
          onInput={(e) => updatePreview(e.currentTarget)}
        >
          <section className="form-section">
            <h2 className="form-section-title">
              <i className="bi bi-building" /> Branch details
            </h2>
            <div className="row g-3">
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="from_branch">From</label>
                <input
                  type="text"
                  name="from_branch"
                  id="from_branch"
                  className="form-control"
                  defaultValue={defaults.from_branch}
                  placeholder="e.g. MAIN OFFICE (LIPA)"
                  required
                  autoComplete="organization"
                />
              </div>
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="to_branch">To</label>
                <input
                  type="text"
                  name="to_branch"
                  id="to_branch"
                  className="form-control"
                  defaultValue={defaults.to_branch}
                  placeholder="e.g. LIPA (LUIS LOYOLA)"
                  required
                  autoComplete="organization"
                />
              </div>
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="released_by">Released by</label>
                <input
                  type="text"
                  name="released_by"
                  id="released_by"
                  className="form-control"
                  defaultValue={defaults.released_by}
                  placeholder="Full name"
                  required
                  autoComplete="name"
                />
              </div>
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="date_released">Date released</label>
                <input
                  type="date"
                  name="date_released"
                  id="date_released"
                  className="form-control"
                  defaultValue={defaults.date_released || today}
                  required
                />
              </div>
            </div>
          </section>

          <section className="form-section">
            <h2 className="form-section-title">
              <i className="bi bi-hash" /> Pad &amp; S.I series
            </h2>
            <div className="row g-3">
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="starting_pad">Starting pad no.</label>
                <input
                  type="number"
                  name="starting_pad"
                  id="starting_pad"
                  className="form-control"
                  defaultValue={defaults.starting_pad}
                  min={1}
                  required
                  inputMode="numeric"
                />
                <div className="hint-auto">
                  <i className="bi bi-lightning-charge" /> Auto from last record — next pad <strong>{nextPad}</strong>
                </div>
              </div>
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="starting_si">Starting S.I no.</label>
                <input
                  type="number"
                  name="starting_si"
                  id="starting_si"
                  className="form-control"
                  defaultValue={defaults.starting_si}
                  min={1}
                  required
                  inputMode="numeric"
                />
                <div className="hint-auto">
                  <i className="bi bi-lightning-charge" /> After last end <strong>{lastSiEnd}</strong> → <strong>{nextSi}</strong>
                </div>
              </div>
              <div className="col-12 col-sm-6">
                <label className="form-label" htmlFor="total_pads">Total pads</label>
                <input
                  type="number"
                  name="total_pads"
                  id="total_pads"
                  className="form-control"
                  defaultValue={defaults.total_pads}
                  min={1}
                  max={500}
                  placeholder="e.g. 5"
                  required
                  inputMode="numeric"
                />
              </div>
            </div>
            <div className="live-preview" aria-live="polite">
              {preview}
            </div>
          </section>

          <div className="info-box">
            <span className="info-box-icon">
              <i className="bi bi-info-lg" />
            </span>
            <div>
              <strong>50 S.I numbers per pad</strong> — e.g. 48751–48800, then 48801–48850. Pad no. and series increment automatically on save.
            </div>
          </div>

          <div className="form-actions">
            <button type="submit" className="btn btn-primary btn-lg">
              <i className="bi bi-printer" /> Generate &amp; Save
            </button>
          </div>
        </form>
      </div>
    </>
  );
}
