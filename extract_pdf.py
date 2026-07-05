#!/usr/bin/env python3
# extract_pdf.py - Extract text and detect features from PDF

import sys
import json
import os
import re

try:
    import fitz  # PyMuPDF
except ImportError:
    print(json.dumps({'error': 'PyMuPDF not installed. Run: pip install PyMuPDF'}))
    sys.exit(1)

try:
    import pdfplumber
except ImportError:
    pdfplumber = None

def extract_pdf_content(pdf_path):
    """Extract text and detect features from PDF"""
    
    result = {
        'text': '',
        'has_image': 0,
        'has_diagram': 0,
        'has_chart': 0,
        'has_logo': 0,
        'page_count': 0,
        'image_count': 0
    }
    
    try:
        # Using PyMuPDF (fitz)
        doc = fitz.open(pdf_path)
        result['page_count'] = len(doc)
        extracted_text = []
        image_count = 0
        
        for page_num in range(len(doc)):
            page = doc[page_num]
            
            # Extract text
            text = page.get_text()
            if text.strip():
                extracted_text.append(text.strip())
            
            # Check for images
            images = page.get_images()
            if images:
                image_count += len(images)
                result['has_image'] = 1
        
        doc.close()
        
        # Combine all text
        full_text = ' '.join(extracted_text)
        result['text'] = full_text
        result['image_count'] = image_count
        
        # Detect features from text
        text_lower = full_text.lower()
        
        # Detect diagram
        diagram_keywords = ['diagram', 'figure', 'fig.', 'schematic', 'flowchart', 
                           'architecture', 'design', 'layout', 'flow', 'process',
                           'class diagram', 'sequence diagram', 'uml', 'erd',
                           'entity relationship', 'block diagram', 'circuit']
        for keyword in diagram_keywords:
            if keyword in text_lower:
                result['has_diagram'] = 1
                break
        
        # Detect chart
        chart_keywords = ['chart', 'graph', 'plot', 'bar chart', 'pie chart', 
                         'line graph', 'histogram', 'scatter', 'trend', 'comparison']
        for keyword in chart_keywords:
            if keyword in text_lower:
                result['has_chart'] = 1
                break
        
        # Detect logo
        logo_keywords = ['logo', 'brand', 'emblem', 'trademark', 'symbol', 
                        'watermark', 'icon', 'company logo']
        for keyword in logo_keywords:
            if keyword in text_lower:
                result['has_logo'] = 1
                break
        
        # If many images, likely has diagrams
        if image_count > 2:
            result['has_image'] = 1
            if image_count > 5:
                result['has_diagram'] = 1
        
        # Method 2: Using pdfplumber for better text extraction (if available)
        if pdfplumber:
            try:
                with pdfplumber.open(pdf_path) as pdf:
                    pdfplumber_text = []
                    for page in pdf.pages:
                        page_text = page.extract_text()
                        if page_text:
                            pdfplumber_text.append(page_text.strip())
                    
                    if pdfplumber_text:
                        combined = ' '.join(pdfplumber_text)
                        if len(combined) > len(full_text):
                            result['text'] = combined
                            
                            text_lower = combined.lower()
                            for keyword in diagram_keywords:
                                if keyword in text_lower:
                                    result['has_diagram'] = 1
                                    break
                            for keyword in chart_keywords:
                                if keyword in text_lower:
                                    result['has_chart'] = 1
                                    break
                            for keyword in logo_keywords:
                                if keyword in text_lower:
                                    result['has_logo'] = 1
                                    break
            except Exception:
                pass
        
        # Clean text
        result['text'] = re.sub(r'\s+', ' ', result['text']).strip()
        
        if not result['text'] and result['has_image']:
            result['text'] = "[This is a scanned or image-based PDF. No text could be extracted.]"
        
    except Exception as e:
        return {'error': str(e)}
    
    return result

def generate_summary(title, features, text, page_count):
    """Generate summary based on extracted content"""
    
    summary_parts = []
    title_lower = title.lower()
    text_lower = text.lower()
    
    topics = []
    
    topic_map = {
        'ai': 'Artificial Intelligence',
        'database': 'Database Management',
        'sql': 'SQL and Database',
        'network': 'Computer Networking',
        'security': 'Cybersecurity',
        'software': 'Software Engineering',
        'programming': 'Programming',
        'design': 'System Design',
        'architecture': 'System Architecture',
        'data': 'Data Management',
        'analysis': 'Data Analysis',
        'development': 'Software Development',
        'cloud': 'Cloud Computing',
        'mobile': 'Mobile Development',
        'web': 'Web Development',
        'machine learning': 'Machine Learning',
        'deep learning': 'Deep Learning',
        'diagram': 'Diagram Design',
        'chart': 'Data Visualization',
        'report': 'Report Documentation',
        'management': 'Project Management',
        'system': 'System Design',
        'implementation': 'System Implementation',
        'algorithm': 'Algorithms',
        'structure': 'Data Structures',
        'normalization': 'Database Normalization',
        'erd': 'Entity Relationship Diagram',
        'uml': 'UML Diagrams'
    }
    
    for keyword, topic in topic_map.items():
        if keyword in title_lower:
            topics.append(topic)
    
    if not topics:
        for keyword, topic in topic_map.items():
            if keyword in text_lower:
                topics.append(topic)
                if len(topics) >= 2:
                    break
    
    if topics:
        if len(topics) == 1:
            summary_parts.append(f"This document focuses on {topics[0]}")
        else:
            topics_str = ', '.join(topics[:-1]) + ' and ' + topics[-1]
            summary_parts.append(f"This document covers {topics_str}")
    else:
        summary_parts.append(f"This document provides comprehensive information about {title}")
    
    features_list = []
    if features.get('has_diagram'): features_list.append('diagrams')
    if features.get('has_chart'): features_list.append('charts')
    if features.get('has_image'): features_list.append('visual illustrations')
    if features.get('has_logo'): features_list.append('branding elements')
    
    if features_list:
        summary_parts.append(f"featuring {', '.join(features_list)}")
    
    if page_count > 0:
        summary_parts.append(f"spanning {page_count} pages")
    
    if text and not text.startswith('[This is a scanned'):
        sentences = re.split(r'[.!?]+', text)
        meaningful_sentences = [s.strip() for s in sentences if len(s.strip()) > 30]
        if meaningful_sentences:
            summary_parts.append(f"The document begins with: \"{meaningful_sentences[0]}.\"")
    
    return '. '.join(summary_parts) + '.'

def generate_keywords(title, features, text):
    """Generate keywords based on content"""
    
    keywords = []
    title_lower = title.lower()
    text_lower = text.lower()
    
    important_keywords = [
        'ai', 'database', 'sql', 'mysql', 'mongodb', 'postgresql', 'oracle',
        'normalization', 'erd', 'diagram', 'uml', 'network', 'security',
        'software', 'programming', 'python', 'java', 'php', 'javascript',
        'html', 'css', 'react', 'angular', 'nodejs', 'api', 'rest',
        'microservices', 'docker', 'kubernetes', 'cloud', 'aws', 'azure',
        'devops', 'agile', 'scrum', 'testing', 'data', 'analytics',
        'design', 'architecture', 'development', 'implementation',
        'management', 'system', 'application', 'mobile', 'web',
        'algorithm', 'structure', 'framework', 'protocol',
        'authentication', 'authorization', 'machine learning',
        'deep learning', 'neural network', 'report', 'analysis',
        'advantages', 'benefits', 'features', 'methodology'
    ]
    
    for keyword in important_keywords:
        if keyword in title_lower:
            keywords.append(keyword)
    
    if len(keywords) < 5:
        for keyword in important_keywords:
            if keyword in text_lower and keyword not in keywords:
                keywords.append(keyword)
                if len(keywords) >= 8:
                    break
    
    if features.get('has_diagram'): keywords.append('diagram')
    if features.get('has_chart'): keywords.append('chart')
    if features.get('has_image'): keywords.append('image')
    if features.get('has_logo'): keywords.append('logo')
    
    if not keywords:
        words = re.findall(r'\b[a-zA-Z]{3,}\b', title)
        keywords = words[:5]
    
    keywords = list(dict.fromkeys(keywords))
    return ', '.join(keywords[:10])

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'No file path provided'}))
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    title = sys.argv[2] if len(sys.argv) > 2 else os.path.basename(pdf_path)
    
    result = extract_pdf_content(pdf_path)
    
    if 'error' in result:
        print(json.dumps(result))
        sys.exit(1)
    
    result['summary'] = generate_summary(
        title, 
        result, 
        result.get('text', ''), 
        result.get('page_count', 0)
    )
    result['keywords'] = generate_keywords(
        title,
        result,
        result.get('text', '')
    )
    
    print(json.dumps(result))