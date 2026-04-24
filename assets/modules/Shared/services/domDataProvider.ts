import { DOM_DATA_ATTRIBUTES } from '@shared/config/dom';

export const getTargetUlid = (): string => getStringValueFromDataset(DOM_DATA_ATTRIBUTES.ULID);

export const getTargetComponent = (): string => getStringValueFromDataset(DOM_DATA_ATTRIBUTES.COMPONENT);

export const getTargetTraceId = (): string => getStringValueFromDataset(DOM_DATA_ATTRIBUTES.TRACE_ID, 'no-trace-id');

export const getStringValueFromDataset = (
    key: string,
    defaultValue: string = ''
): string => getRootElement()?.dataset[key] ?? defaultValue;

export const getRootElement = (): HTMLElement | null => document.getElementById(APP_ID);

export const APP_ID = 'app' as const;
